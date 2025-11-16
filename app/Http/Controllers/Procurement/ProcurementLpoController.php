<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\Requisition;
use App\Models\RequisitionApproval; // ADD THIS IMPORT
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StoreService;

class ProcurementLpoController extends Controller
{
    public function index()
    {
        $lpos = Lpo::with(['requisition', 'supplier', 'items'])
            ->latest()
            ->paginate(10);

        return view('procurement.lpos.index', compact('lpos'));
    }

    public function show(Lpo $lpo)
    {
        $lpo->load([
            'requisition',
            'requisition.project',
            'requisition.requester',
            'supplier',
            'items'
        ]);

        return view('procurement.lpos.show', compact('lpo'));
    }

   public function markDelivered(Lpo $lpo)
{
    if ($lpo->status !== 'issued') {
        abort(403, 'Only issued LPOs can be marked as delivered.');
    }

    DB::beginTransaction();
    try {
        // Update LPO status
        $lpo->update([
            'status' => 'delivered',
            'delivery_date' => now(),
        ]);

        // Update requisition status
        $lpo->requisition->update([
            'status' => Requisition::STATUS_DELIVERED
        ]);

        // Process LPO items to store inventory
        $storeService = new StoreService();
        $storeService->processDeliveredLpo($lpo);

        // Create approval record
        RequisitionApproval::create([
            'requisition_id' => $lpo->requisition_id,
            'approved_by' => auth()->id(),
            'role' => 'procurement',
            'action' => 'delivered',
            'comment' => 'Items delivered by supplier for LPO: ' . $lpo->lpo_number,
        ]);

        DB::commit();

        return redirect()->route('procurement.lpos.show', $lpo)
            ->with('success', 'LPO marked as delivered! Items received from supplier and added to project store.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to mark LPO as delivered: ' . $e->getMessage());
    }
}

    // Add the issueLpo method if it's missing
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