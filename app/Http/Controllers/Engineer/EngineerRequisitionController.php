<?php

namespace App\Http\Controllers\Engineer;

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

class EngineerRequisitionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $requisitions = Requisition::where('requested_by', $user->id)
            ->with(['project', 'items', 'approvals'])
            ->latest()
            ->paginate(10);

        // Get projects assigned to this engineer
        $projects = $user->projects()->get();

        return view('engineer.requisitions.index', compact('requisitions', 'projects'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Get projects assigned to this engineer
        $projects = $user->projects()->get();

        // Get project stores with their inventory
        $projectStores = Store::whereHas('project.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['inventoryItems' => function($query) {
            $query->where('quantity', '>', 0);
        }])->get();

        return view('engineer.requisitions.create', compact('projects', 'projectStores'));
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
            'items.*.from_store' => 'nullable|boolean',
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

            // Create requisition - engineer requisitions always start as 'pending'
            $requisition = Requisition::create([
                'ref' => $ref,
                'project_id' => $validated['project_id'],
                'requested_by' => $user->id,
                'type' => $validated['type'],
                'urgency' => $validated['urgency'],
                'status' => 'pending', // Engineer requisitions need Project Manager approval
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
                    'from_store' => $itemData['from_store'] ?? false,
                ]);
            }

            // Create initial approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => $user->id,
                'role' => 'engineer',
                'action' => 'created',
                'comment' => 'Requisition created and submitted for Project Manager approval',
            ]);

            DB::commit();

            return redirect()->route('engineer.requisitions.index')
                ->with('success', 'Requisition created successfully! Awaiting Project Manager approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create requisition: ' . $e->getMessage());
        }
    }

   // In your EngineerRequisitionController show method
public function show(Requisition $requisition)
{
    // Verify the requisition belongs to the current engineer
    if ($requisition->requested_by !== auth()->id()) {
        abort(403, 'Unauthorized access to this requisition.');
    }

    $requisition->load([
        'project',
        'items', 
        'approvals.approver',
        'lpo.items',
        'lpo.receivedItems.lpoItem' // Add this line
    ]);

    return view('engineer.requisitions.show', compact('requisition'));
}

    public function pending()
    {
        $user = auth()->user();
        
        // Get requisitions created by this engineer that are pending
        $pendingRequisitions = Requisition::where('requested_by', $user->id)
            ->where('status', 'pending')
            ->with(['project', 'items'])
            ->latest()
            ->paginate(10);

        return view('engineer.requisitions.pending', compact('pendingRequisitions'));
    }

    private function authorizeRequisitionAccess(Requisition $requisition)
    {
        $user = auth()->user();
        
        // Check if user owns the requisition
        if ($requisition->requested_by !== $user->id) {
            abort(403, 'Unauthorized access to this requisition.');
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
}