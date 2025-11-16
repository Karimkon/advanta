<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsRequisitionController extends Controller
{
    public function index()
    {
        $requisitions = Requisition::whereIn('status', [
            Requisition::STATUS_PROJECT_MANAGER_APPROVED,
            Requisition::STATUS_OPERATIONS_APPROVED,
            Requisition::STATUS_PROCUREMENT
        ])
        ->with(['project', 'requester', 'items', 'approvals'])
        ->latest()
        ->paginate(10);

        return view('operations.requisitions.index', compact('requisitions'));
    }

    public function pending()
    {
        $pendingRequisitions = Requisition::where('status', Requisition::STATUS_PROJECT_MANAGER_APPROVED)
            ->with(['project', 'requester', 'items'])
            ->latest()
            ->paginate(10);

        return view('operations.requisitions.pending', compact('pendingRequisitions'));
    }

    public function approved()
    {
        $approvedRequisitions = Requisition::where('status', Requisition::STATUS_OPERATIONS_APPROVED)
            ->with(['project', 'requester', 'items'])
            ->latest()
            ->paginate(10);

        return view('operations.requisitions.approved', compact('approvedRequisitions'));
    }

    public function show(Requisition $requisition)
    {
        // Authorization - ensure requisition is in correct status for operations
        if (!$requisition->canBeApprovedBy('operations')) {
            abort(403, 'This requisition is not ready for operations approval.');
        }

        $requisition->load([
            'project', 
            'requester', 
            'items', 
            'approvals.approver',
            'store'
        ]);

        return view('operations.requisitions.show', compact('requisition'));
    }

    public function approve(Requisition $requisition)
    {
        // Authorization
        if (!$requisition->canBeApprovedBy('operations')) {
            abort(403, 'This requisition is not ready for operations approval.');
        }

        $validated = request()->validate([
            'comment' => 'nullable|string|max:500',
            'approved_amount' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            // Update requisition status
            $requisition->update([
                'status' => Requisition::STATUS_OPERATIONS_APPROVED
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'operations',
                'action' => 'approved',
                'comment' => $validated['comment'] ?? 'Approved by Operations',
                'approved_amount' => $validated['approved_amount'] ?? $requisition->estimated_total,
            ]);

            DB::commit();

            return redirect()->route('operations.requisitions.pending')
                ->with('success', 'Requisition approved and sent to procurement!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve requisition: ' . $e->getMessage());
        }
    }

    public function reject(Requisition $requisition)
    {
        // Authorization
        if (!$requisition->canBeApprovedBy('operations')) {
            abort(403, 'This requisition is not ready for operations approval.');
        }

        $validated = request()->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Update requisition status
            $requisition->update([
                'status' => Requisition::STATUS_REJECTED
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'operations',
                'action' => 'rejected',
                'comment' => $validated['rejection_reason'],
            ]);

            DB::commit();

            return redirect()->route('operations.requisitions.pending')
                ->with('success', 'Requisition rejected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject requisition: ' . $e->getMessage());
        }
    }

    public function sendToProcurement(Requisition $requisition)
    {
        // Authorization - only for operations approved requisitions
        if ($requisition->status !== Requisition::STATUS_OPERATIONS_APPROVED) {
            abort(403, 'Only operations-approved requisitions can be sent to procurement.');
        }

        DB::beginTransaction();
        try {
            // Update requisition status
            $requisition->update([
                'status' => Requisition::STATUS_PROCUREMENT
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'operations',
                'action' => 'forwarded',
                'comment' => 'Sent to Procurement Department',
            ]);

            DB::commit();

            return redirect()->route('operations.requisitions.index')
                ->with('success', 'Requisition sent to procurement successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to send requisition to procurement: ' . $e->getMessage());
        }
    }
}