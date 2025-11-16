<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
use App\Models\Supplier;
use App\Models\Lpo;
use App\Models\LpoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementRequisitionController extends Controller
{
    public function index()
    {
        $requisitions = Requisition::whereIn('status', [
            Requisition::STATUS_OPERATIONS_APPROVED,
            Requisition::STATUS_PROCUREMENT,
            Requisition::STATUS_CEO_APPROVED,
            Requisition::STATUS_LPO_ISSUED
        ])
        ->with(['project', 'requester', 'items', 'approvals', 'lpo'])
        ->latest()
        ->paginate(10);

        return view('procurement.requisitions.index', compact('requisitions'));
    }

    public function pending()
    {
        $pendingRequisitions = Requisition::where('status', Requisition::STATUS_OPERATIONS_APPROVED)
            ->with(['project', 'requester', 'items'])
            ->latest()
            ->paginate(10);

        return view('procurement.requisitions.pending', compact('pendingRequisitions'));
    }

    // ADD THIS METHOD - FIX THE ISSUE
    public function inProcurement()
    {
        $procurementRequisitions = Requisition::where('status', Requisition::STATUS_PROCUREMENT)
            ->with(['project', 'requester', 'items', 'lpo'])
            ->latest()
            ->paginate(10);

        return view('procurement.requisitions.in-procurement', compact('procurementRequisitions'));
    }

    public function show(Requisition $requisition)
{
    // Authorization - allow procurement to view requisitions in various statuses
    if (!in_array($requisition->status, [
        Requisition::STATUS_OPERATIONS_APPROVED,
        Requisition::STATUS_PROCUREMENT,
        Requisition::STATUS_CEO_APPROVED,
        Requisition::STATUS_LPO_ISSUED,
        Requisition::STATUS_DELIVERED, // ADD THIS - Allow viewing delivered requisitions
        Requisition::STATUS_COMPLETED  // ADD THIS - Allow viewing completed requisitions
    ])) {
        abort(403, 'This requisition is not ready for procurement processing.');
    }

    $requisition->load([
        'project', 
        'requester', 
        'items', 
        'approvals.approver',
        'lpo',
        'lpo.supplier'
    ]);

    $suppliers = Supplier::where('status', 'active')->get();

    return view('procurement.requisitions.show', compact('requisition', 'suppliers'));
}

    public function sendToCEO(Requisition $requisition)
    {
        // Authorization
        if ($requisition->status !== Requisition::STATUS_PROCUREMENT) {
            abort(403, 'Only procurement-processed requisitions can be sent to CEO.');
        }

        $validated = request()->validate([
            'comment' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Update requisition status
            $requisition->update([
                'status' => Requisition::STATUS_CEO_APPROVED
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'procurement',
                'action' => 'forwarded',
                'comment' => $validated['comment'] ?? 'Sent to CEO for final approval',
            ]);

            DB::commit();

            return redirect()->route('procurement.requisitions.in-procurement')
                ->with('success', 'Requisition sent to CEO for final approval!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to send requisition to CEO: ' . $e->getMessage());
        }
    }

    public function startProcurement(Requisition $requisition)
    {
        // Authorization
        if ($requisition->status !== Requisition::STATUS_OPERATIONS_APPROVED) {
            abort(403, 'Only operations-approved requisitions can start procurement.');
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
                'role' => 'procurement',
                'action' => 'started',
                'comment' => 'Procurement process started - sourcing suppliers',
            ]);

            DB::commit();

            return redirect()->route('procurement.requisitions.show', $requisition)
                ->with('success', 'Procurement process started! You can now create LPO.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to start procurement: ' . $e->getMessage());
        }
    }

  public function createLpo(Requisition $requisition)
{
    try {
        $supplier_id = request('supplier_id');
        $notes = request('notes');
        
        if (!$supplier_id) {
            return back()->with('error', 'Supplier is required.');
        }

        // Generate LPO number
        $lpoNumber = 'LPO-' . date('Ymd') . '-' . rand(1000, 9999);

        // Calculate totals
        $subtotal = $requisition->estimated_total;
        $total = $subtotal;

        // Create LPO
        $lpo = Lpo::create([
            'lpo_number' => $lpoNumber,
            'requisition_id' => $requisition->id,
            'supplier_id' => $supplier_id,
            'status' => 'draft',
            'subtotal' => $subtotal,
            'tax' => 0,
            'other_charges' => 0,
            'total' => $total,
            'notes' => $notes,
        ]);

        // Create LPO items
        foreach ($requisition->items as $item) {
            LpoItem::create([
                'lpo_id' => $lpo->id,
                'inventory_item_id' => null,
                'description' => $item->name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        // ✅ FIX: Update requisition status to CEO_APPROVED (not procurement)
        $requisition->update([
            'status' => Requisition::STATUS_CEO_APPROVED
        ]);

        // Create approval record
        RequisitionApproval::create([
            'requisition_id' => $requisition->id,
            'approved_by' => auth()->id(),
            'role' => 'procurement',
            'action' => 'lpo_created',
            'comment' => 'LPO created and sent to CEO for approval: ' . $lpoNumber,
        ]);

        return redirect()->route('procurement.lpos.show', $lpo)
            ->with('success', 'LPO created successfully! Sent to CEO for final approval.');

    } catch (\Exception $e) {
        \Log::error('LPO Creation Error: ' . $e->getMessage());
        return back()->with('error', 'Failed to create LPO: ' . $e->getMessage());
    }
}

    public function issueLpo(Lpo $lpo)
    {
        if ($lpo->status !== 'draft') {
            abort(403, 'Only draft LPOs can be issued.');
        }

        DB::beginTransaction();
        try {
            // Update LPO status
            $lpo->update([
                'status' => 'issued',
                'issue_date' => now(),
            ]);

            // Update requisition status
            $lpo->requisition->update([
                'status' => Requisition::STATUS_LPO_ISSUED
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $lpo->requisition_id,
                'approved_by' => auth()->id(),
                'role' => 'procurement',
                'action' => 'lpo_issued',
                'comment' => 'LPO issued to supplier: ' . $lpo->lpo_number,
            ]);

            DB::commit();

            return redirect()->route('procurement.lpos.show', $lpo)
                ->with('success', 'LPO issued to supplier successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to issue LPO: ' . $e->getMessage());
        }
    }
}