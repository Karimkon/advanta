<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\RequisitionItem;
use App\Models\RequisitionApproval;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectManagerRequisitionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $requisitions = Requisition::where('requested_by', $user->id)
            ->with(['project', 'items', 'approvals'])
            ->latest()
            ->paginate(10);

         // Get projects for filter
        $projects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();  

        return view('project_manager.requisitions.index', compact('requisitions', 'projects'));
    }

   public function create()
{
    $user = auth()->user();
    
    // Get projects managed by this project manager
    $projects = Project::whereHas('users', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get();

    // Get project stores with their inventory
    $projectStores = Store::whereHas('project.users', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->with(['inventoryItems' => function($query) {
        $query->where('quantity', '>', 0);
    }])->get();

    return view('project_manager.requisitions.create', compact('projects', 'projectStores'));
}

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type' => 'required|in:store,purchase',
            'urgency' => 'required|in:low,medium,high',
            'reason' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        // Validate store_id for store requisitions
        if ($request->type === 'store') {
            $request->validate(['store_id' => 'required|exists:stores,id']);
        }

        DB::beginTransaction();
        try {
            // Generate reference number
            $ref = 'REQ-' . strtoupper(Str::random(8));

            // Calculate total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }

            // For purchase requisitions created by Project Manager, auto-approve and send to operations
            $status = $validated['type'] === 'purchase' ? 'project_manager_approved' : 'pending';

            // Create requisition
            $requisition = Requisition::create([
                'ref' => $ref,
                'project_id' => $validated['project_id'],
                'requested_by' => $user->id,
                'type' => $validated['type'],
                'urgency' => $validated['urgency'],
                'status' => $status, // CHANGED THIS LINE
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
                'store_id' => $request->store_id,
                'attachments' => $this->handleAttachments($request),
            ]);

            // Create requisition items
            foreach ($validated['items'] as $itemData) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'name' => $itemData['name'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Create initial approval record
            $action = $validated['type'] === 'purchase' ? 'auto_approved' : 'created';
            $comment = $validated['type'] === 'purchase' 
                ? 'Purchase requisition created and auto-approved by Project Manager' 
                : 'Requisition created and submitted for approval';

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => $user->id,
                'role' => 'project_manager',
                'action' => $action,
                'comment' => $comment,
            ]);

            DB::commit();

            $message = $validated['type'] === 'purchase' 
                ? 'Purchase requisition created and sent to Operations!' 
                : 'Store requisition created successfully!';

            return redirect()->route('project_manager.requisitions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create requisition: ' . $e->getMessage());
        }
    }

    

    public function show(Requisition $requisition)
    {
        // Authorization - ensure project manager owns this requisition or manages the project
        $this->authorizeRequisitionAccess($requisition);

        $requisition->load([
            'project', 
            'requester', 
            'items', 
            'approvals.approver',
            'lpo.supplier',
            'store'
        ]);

        return view('project_manager.requisitions.show', compact('requisition'));
    }

    public function pending()
    {
        $user = auth()->user();
        
        // Get pending requisitions that need project manager approval
        $pendingRequisitions = Requisition::whereHas('project', function($query) use ($user) {
            $query->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })
        ->where('status', Requisition::STATUS_PENDING)
        ->where('type', Requisition::TYPE_STORE) // Only store requisitions
        ->with(['project', 'requester', 'items'])
        ->latest()
        ->paginate(20);

        return view('project_manager.requisitions.pending', compact('pendingRequisitions'));
    }

    public function approve(Requisition $requisition)
    {
        // Authorization - ensure project manager can approve this requisition
        $this->authorizeRequisitionApproval($requisition);

        DB::beginTransaction();
        try {
            $nextStatus = $requisition->isStoreRequisition() 
                ? 'project_manager_approved' 
                : 'project_manager_approved';

            $requisition->update(['status' => $nextStatus]);

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => 'approved',
                'comment' => request('comment', 'Approved by Project Manager'),
                'approved_amount' => $requisition->estimated_total,
            ]);

            DB::commit();

            return back()->with('success', 'Requisition approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve requisition: ' . $e->getMessage());
        }
    }

    public function reject(Requisition $requisition)
    {
        // Authorization - ensure project manager can approve this requisition
        $this->authorizeRequisitionApproval($requisition);

        $validated = request()->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $requisition->update(['status' => 'rejected']);

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => 'rejected',
                'comment' => $validated['rejection_reason'],
            ]);

            DB::commit();

            return back()->with('success', 'Requisition rejected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject requisition: ' . $e->getMessage());
        }
    }

    private function authorizeRequisitionAccess(Requisition $requisition)
    {
        $user = auth()->user();
        
        // Check if user owns the requisition or manages the project
        if ($requisition->requested_by !== $user->id && 
            !$requisition->project->users->contains($user->id)) {
            abort(403, 'Unauthorized access to this requisition.');
        }
    }

    private function authorizeRequisitionApproval(Requisition $requisition)
    {
        $user = auth()->user();
        
        // Check if user manages the project and requisition is pending
        if (!$requisition->project->users->contains($user->id) || 
            $requisition->status !== 'pending') {
            abort(403, 'Unauthorized to approve this requisition.');
        }
    }

    private function handleAttachments(Request $request)
    {
        if (!$request->hasFile('attachments')) {
            return null;
        }

        $attachments = [];
        foreach ($request->file('attachments') as $file) {
            if ($file->isValid()) {
                $path = $file->store('requisitions/attachments', 'public');
                $attachments[] = $path;
            }
        }

        return json_encode($attachments);
    }

    public function edit(Requisition $requisition)
    {
        // Authorization - ensure project manager owns this requisition
        $this->authorizeRequisitionAccess($requisition);

        // Only allow editing of pending requisitions
        if (!$requisition->canBeEdited()) {
            return redirect()->route('project_manager.requisitions.show', $requisition)
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $user = auth()->user();
        
        // Get projects managed by this project manager
        $projects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Get project stores with their inventory
        $projectStores = Store::whereHas('project.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        $requisition->load('items');

        return view('project_manager.requisitions.edit', compact('requisition', 'projects', 'projectStores'));
    }

    public function update(Request $request, Requisition $requisition)
    {
        // Authorization - ensure project manager owns this requisition
        $this->authorizeRequisitionAccess($requisition);

        // Only allow editing of pending requisitions
        if (!$requisition->canBeEdited()) {
            return back()->with('error', 'Only pending requisitions can be edited.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'urgency' => 'required|in:low,medium,high',
            'reason' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Calculate new total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }

            // Store old values for audit trail
            $oldValues = [
                'project_id' => $requisition->project_id,
                'urgency' => $requisition->urgency,
                'reason' => $requisition->reason,
                'estimated_total' => $requisition->estimated_total,
            ];

            // Update requisition
            $requisition->update([
                'project_id' => $validated['project_id'],
                'urgency' => $validated['urgency'],
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
                'attachments' => $this->handleAttachments($request, $requisition->attachments),
            ]);

            // Delete existing items and create new ones
            $requisition->items()->delete();
            foreach ($validated['items'] as $itemData) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'name' => $itemData['name'],
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Create edit approval record for audit trail
            $changes = $this->getChangesDescription($oldValues, $requisition);
            
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => 'edited',
                'comment' => 'Requisition updated: ' . $changes,
            ]);

            DB::commit();

            return redirect()->route('project_manager.requisitions.show', $requisition)
                ->with('success', 'Requisition updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update requisition: ' . $e->getMessage());
        }
    }

    /**
     * Generate description of changes for audit trail
     */
    private function getChangesDescription($oldValues, $requisition)
    {
        $changes = [];
        
        if ($oldValues['project_id'] != $requisition->project_id) {
            $oldProject = Project::find($oldValues['project_id']);
            $newProject = Project::find($requisition->project_id);
            $changes[] = "Project changed from {$oldProject->name} to {$newProject->name}";
        }
        
        if ($oldValues['urgency'] != $requisition->urgency) {
            $changes[] = "Urgency changed from {$oldValues['urgency']} to {$requisition->urgency}";
        }
        
        if ($oldValues['estimated_total'] != $requisition->estimated_total) {
            $changes[] = "Total amount changed from UGX " . number_format($oldValues['estimated_total'], 2) . " to UGX " . number_format($requisition->estimated_total, 2);
        }
        
        if ($oldValues['reason'] != $requisition->reason) {
            $changes[] = "Reason updated";
        }
        
        return $changes ? implode(', ', $changes) : 'Details updated';
    }
}