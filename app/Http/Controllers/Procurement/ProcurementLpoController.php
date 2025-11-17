<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
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
            'items',
            'preparer'
        ]);

        return view('procurement.lpos.show', compact('lpo'));
    }
public function markDelivered(Lpo $lpo)
{
    \Log::info('Mark as Delivered called for LPO:', [
        'lpo_id' => $lpo->id,
        'current_status' => $lpo->status,
        'item_count' => $lpo->items->count(),
        'total_items_value' => $lpo->items->sum('total_price')
    ]);

    if ($lpo->status !== 'issued') {
        \Log::error('LPO not in issued status. Current status: ' . $lpo->status);
        abort(403, 'Only issued LPOs can be marked as delivered. Current status: ' . $lpo->status);
    }

    if ($lpo->items->count() === 0) {
        \Log::error('LPO has no items to deliver', ['lpo_id' => $lpo->id]);
        return back()->with('error', 'Cannot mark LPO as delivered: No items found in this LPO.');
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

        \Log::info('Processing LPO items for store inventory:', [
            'lpo_id' => $lpo->id,
            'items_count' => $lpo->items->count()
        ]);

        // Process LPO items to store inventory
        $storeService = new StoreService();
        $result = $storeService->processDeliveredLpo($lpo);

        \Log::info('Store service result:', ['result' => $result]);

        // Create approval record
        RequisitionApproval::create([
            'requisition_id' => $lpo->requisition_id,
            'approved_by' => auth()->id(),
            'role' => 'procurement',
            'action' => 'delivered',
            'comment' => 'Items delivered by supplier for LPO: ' . $lpo->lpo_number,
        ]);

        DB::commit();

        \Log::info('LPO marked as delivered successfully:', [
            'lpo_id' => $lpo->id,
            'items_processed' => $lpo->items->count()
        ]);

        return redirect()->route('procurement.lpos.show', $lpo)
            ->with('success', 'LPO marked as delivered! Items received from supplier and added to project store.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to mark LPO as delivered:', [
            'lpo_id' => $lpo->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Failed to mark LPO as delivered: ' . $e->getMessage());
    }
}
}