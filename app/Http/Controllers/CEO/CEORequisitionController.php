<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
use App\Models\Lpo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CEORequisitionController extends Controller
{
    public function index()
    {
        $requisitions = Requisition::whereIn('status', [
            Requisition::STATUS_CEO_APPROVED,
            Requisition::STATUS_LPO_ISSUED,
            Requisition::STATUS_DELIVERED
        ])
        ->with(['project', 'requester', 'items', 'lpo', 'lpo.supplier'])
        ->latest()
        ->paginate(10);

        return view('ceo.requisitions.index', compact('requisitions'));
    }

    public function pending()
    {
        $pendingRequisitions = Requisition::where('status', Requisition::STATUS_CEO_APPROVED)
            ->with(['project', 'requester', 'items', 'lpo', 'lpo.supplier'])
            ->latest()
            ->paginate(10);

        return view('ceo.requisitions.pending', compact('pendingRequisitions'));
    }

    public function show(Requisition $requisition)
    {
        // Authorization - ensure requisition is in correct status for CEO
        if (!in_array($requisition->status, [
            Requisition::STATUS_CEO_APPROVED,
            Requisition::STATUS_LPO_ISSUED,
            Requisition::STATUS_DELIVERED
        ])) {
            abort(403, 'This requisition is not ready for CEO review.');
        }

        $requisition->load([
            'project', 
            'requester', 
            'items', 
            'approvals.approver',
            'lpo',
            'lpo.supplier',
            'lpo.items'
        ]);

        return view('ceo.requisitions.show', compact('requisition'));
    }

    public function approveRequisition(Requisition $requisition)
    {
        if ($requisition->status !== Requisition::STATUS_CEO_APPROVED) {
            abort(403, 'Only CEO-approved status requisitions can be processed.');
        }

        $validated = request()->validate([
            'comment' => 'nullable|string|max:500',
            'approved_amount' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            // Update requisition status remains as CEO_APPROVED (LPO needs to be issued)
            // The status will change when LPO is issued by procurement

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'ceo',
                'action' => 'approved',
                'comment' => $validated['comment'] ?? 'Approved by CEO',
                'approved_amount' => $validated['approved_amount'] ?? $requisition->estimated_total,
            ]);

            DB::commit();

            return redirect()->route('ceo.requisitions.pending')
                ->with('success', 'Requisition approved successfully! Procurement can now issue the LPO.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve requisition: ' . $e->getMessage());
        }
    }

    public function approveLpo(Lpo $lpo)
    {
        if ($lpo->status !== 'draft') {
            abort(403, 'Only draft LPOs can be approved.');
        }

        if ($lpo->requisition->status !== Requisition::STATUS_CEO_APPROVED) {
            abort(403, 'Only CEO-approved requisitions can have LPOs issued.');
        }

        $validated = request()->validate([
            'comment' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Update LPO status to issued
            $lpo->update([
                'status' => 'issued',
                'issued_at' => now(),
                'issued_by' => auth()->id(),
            ]);

            // Update requisition status
            $lpo->requisition->update([
                'status' => Requisition::STATUS_LPO_ISSUED
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $lpo->requisition_id,
                'approved_by' => auth()->id(),
                'role' => 'ceo',
                'action' => 'lpo_approved',
                'comment' => $validated['comment'] ?? 'LPO approved and issued: ' . $lpo->lpo_number,
            ]);

            DB::commit();

            return redirect()->route('ceo.requisitions.pending')
                ->with('success', 'LPO approved and issued successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve LPO: ' . $e->getMessage());
        }
    }

    public function rejectRequisition(Requisition $requisition)
    {
        if ($requisition->status !== Requisition::STATUS_CEO_APPROVED) {
            abort(403, 'Only CEO-approved status requisitions can be rejected.');
        }

        $validated = request()->validate([
            'comment' => 'required|string|max:500'
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
                'role' => 'ceo',
                'action' => 'rejected',
                'comment' => $validated['comment'],
            ]);

            DB::commit();

            return redirect()->route('ceo.requisitions.pending')
                ->with('success', 'Requisition rejected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject requisition: ' . $e->getMessage());
        }
    }
}