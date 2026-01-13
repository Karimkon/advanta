<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\RequisitionItem;
use App\Models\RequisitionApproval;
use App\Models\User;
use App\Exports\RequisitionsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminRequisitionController extends Controller
{
    public function index()
    {
        return view('admin.requisitions.index', [
            'requisitions' => Requisition::with(['project', 'requester'])->latest()->get()
        ]);
    }

    public function create()
    {
        return view('admin.requisitions.create', [
            'projects' => Project::all(),
            'users' => User::whereIn('role', ['site_manager', 'project_manager'])->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'requested_by' => 'required|exists:users,id',
            'urgency' => 'required|in:low,medium,high',
            'reason' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        DB::beginTransaction();
        try {
            // Generate reference number
            $ref = 'REQ-' . strtoupper(Str::random(8));

            // Calculate total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }

            // Create requisition
            $requisition = Requisition::create([
                'ref' => $ref,
                'project_id' => $validated['project_id'],
                'requested_by' => $validated['requested_by'],
                'urgency' => $validated['urgency'],
                'status' => 'pending',
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
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
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'admin',
                'action' => 'created',
                'comment' => 'Requisition created and submitted for approval',
            ]);

            DB::commit();

            return redirect()->route('admin.requisitions.index')
                ->with('success', 'Requisition created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create requisition: ' . $e->getMessage());
        }
    }

    public function show(Requisition $requisition)
    {
        $requisition->load([
            'project', 
            'requester', 
            'items', 
            'approvals.approver',
            'lpo.supplier',
            'lpo.items'
        ]);

        return view('admin.requisitions.show', compact('requisition'));
    }

    public function edit(Requisition $requisition)
    {
        if ($requisition->status !== 'pending') {
            return redirect()->route('admin.requisitions.index')
                ->with('error', 'Only pending requisitions can be edited.');
        }

        return view('admin.requisitions.edit', [
            'requisition' => $requisition->load('items'),
            'projects' => Project::all(),
            'users' => User::whereIn('role', ['site_manager', 'project_manager'])->get()
        ]);
    }

    public function update(Request $request, Requisition $requisition)
    {
        if ($requisition->status !== 'pending') {
            return back()->with('error', 'Only pending requisitions can be updated.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'requested_by' => 'required|exists:users,id',
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

            // Update requisition
            $requisition->update([
                'project_id' => $validated['project_id'],
                'requested_by' => $validated['requested_by'],
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

            // Add update approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'admin',
                'action' => 'updated',
                'comment' => 'Requisition details updated',
            ]);

            DB::commit();

            return redirect()->route('admin.requisitions.show', $requisition)
                ->with('success', 'Requisition updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update requisition: ' . $e->getMessage());
        }
    }

    public function destroy(Requisition $requisition)
    {
        if ($requisition->status !== 'pending') {
            return back()->with('error', 'Only pending requisitions can be deleted.');
        }

        DB::beginTransaction();
        try {
            $requisition->items()->delete();
            $requisition->approvals()->delete();
            $requisition->delete();

            DB::commit();

            return redirect()->route('admin.requisitions.index')
                ->with('success', 'Requisition deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete requisition: ' . $e->getMessage());
        }
    }

    public function approve(Requisition $requisition)
{
    $userRole = auth()->user()->role;
    
    if (!$requisition->canBeApprovedBy($userRole)) {
        return back()->with('error', 'You cannot approve this requisition at this stage.');
    }

    DB::beginTransaction();
    try {
        $nextStatus = $this->getNextStatus($requisition, $userRole);
        $requisition->update(['status' => $nextStatus]);

        // Create approval record
        RequisitionApproval::create([
            'requisition_id' => $requisition->id,
            'approved_by' => auth()->id(),
            'role' => $userRole,
            'action' => 'approved',
            'comment' => request('comment'),
            'approved_amount' => $requisition->estimated_total,
        ]);

        // Handle specific actions based on role and type
        $this->handlePostApprovalActions($requisition, $userRole, $nextStatus);

        DB::commit();

        return back()->with('success', 'Requisition approved successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to approve requisition: ' . $e->getMessage());
    }
}

private function getNextStatus(Requisition $requisition, string $userRole)
{
    if ($requisition->isStoreRequisition()) {
        return match($userRole) {
            'project_manager' => Requisition::STATUS_PROJECT_MANAGER_APPROVED,
            'stores' => Requisition::STATUS_COMPLETED,
            default => $requisition->status
        };
    } else {
        return match($userRole) {
            'project_manager' => Requisition::STATUS_PROJECT_MANAGER_APPROVED,
            'operations' => Requisition::STATUS_OPERATIONS_APPROVED,
            'procurement' => Requisition::STATUS_PROCUREMENT,
            'finance' => Requisition::STATUS_LPO_ISSUED,
            'ceo' => Requisition::STATUS_COMPLETED,
            default => $requisition->status
        };
    }
}

private function handlePostApprovalActions(Requisition $requisition, string $userRole, string $nextStatus)
{
    // Create store release for approved store requisitions
    if ($requisition->isStoreRequisition() && $userRole === 'project_manager') {
        StoreRelease::create([
            'requisition_id' => $requisition->id,
            'store_id' => $requisition->store_id,
            'status' => StoreRelease::STATUS_PENDING,
        ]);

        // Create notification for store officer
        $this->createNotificationForStoreOfficer($requisition);
    }

    // Create LPO for procurement-approved purchase requisitions  
    if ($requisition->isPurchaseRequisition() && $userRole === 'procurement') {
        $this->createLPO($requisition);
    }
}

    public function reject(Requisition $requisition)
    {
        $validated = request()->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $requisition->update(['status' => 'rejected']);

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'admin',
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

    public function sendToProcurement(Requisition $requisition)
    {
        if ($requisition->status !== 'approved') {
            return back()->with('error', 'Only approved requisitions can be sent to procurement.');
        }

        DB::beginTransaction();
        try {
            $requisition->update(['status' => 'sent_to_procurement']);

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'admin',
                'action' => 'forwarded',
                'comment' => 'Requisition forwarded to procurement department',
            ]);

            DB::commit();

            return back()->with('success', 'Requisition forwarded to Procurement successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to forward requisition: ' . $e->getMessage());
        }
    }

    private function handleAttachments(Request $request, $existingAttachments = null)
    {
        if (!$request->hasFile('attachments')) {
            return $existingAttachments;
        }

        $attachments = $existingAttachments ? json_decode($existingAttachments, true) : [];

        foreach ($request->file('attachments') as $file) {
            if ($file->isValid()) {
                $path = $file->store('requisitions/attachments', 'public');
                $attachments[] = $path;
            }
        }

        return json_encode($attachments);
    }

    /**
     * Export requisitions to Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'project_id', 'date_from', 'date_to']);
        return Excel::download(new RequisitionsExport($filters), 'requisitions_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export requisitions to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Requisition::with(['project', 'requestedBy', 'items', 'supplier']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $requisitions = $query->latest()->get();

        $pdf = Pdf::loadView('exports.pdf.requisitions', [
            'requisitions' => $requisitions,
            'title' => 'Requisitions Report'
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('requisitions_' . date('Y-m-d') . '.pdf');
    }
}   