<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem; 
use App\Models\RequisitionApproval;
use App\Models\Lpo;
use App\Models\LpoItem; 
use App\Models\Supplier;
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
            Requisition::STATUS_LPO_ISSUED,
            Requisition::STATUS_DELIVERED
        ])
        ->with(['project', 'requester', 'items', 'lpo'])
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

    public function inProcurement()
{
    $procurementRequisitions = Requisition::whereIn('status', [
        Requisition::STATUS_PROCUREMENT,
        Requisition::STATUS_CEO_APPROVED
    ])
    ->with(['project', 'requester', 'items', 'lpo'])
    ->latest()
    ->paginate(10);

    return view('procurement.requisitions.in-procurement', compact('procurementRequisitions'));
}

    public function show(Requisition $requisition)
    {
        // Allow procurement to view requisitions they handle
        $allowedStatuses = [
            Requisition::STATUS_OPERATIONS_APPROVED,
            Requisition::STATUS_PROCUREMENT,
            Requisition::STATUS_CEO_APPROVED,
            Requisition::STATUS_LPO_ISSUED,
            Requisition::STATUS_DELIVERED
        ];

        if (!in_array($requisition->status, $allowedStatuses)) {
            abort(403, 'This requisition is not available for procurement view.');
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

        $suppliers = Supplier::all();

        return view('procurement.requisitions.show', compact('requisition', 'suppliers'));
    }

    public function startProcurement(Requisition $requisition)
    {
        if ($requisition->status !== Requisition::STATUS_OPERATIONS_APPROVED) {
            abort(403, 'Only operations-approved requisitions can start procurement.');
        }

        DB::beginTransaction();
        try {
            // Update requisition status to procurement
            $requisition->update([
                'status' => Requisition::STATUS_PROCUREMENT
            ]);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'procurement',
                'action' => 'procurement_started',
                'comment' => 'Procurement process initiated',
            ]);

            DB::commit();

            return redirect()->route('procurement.requisitions.show', $requisition)
                ->with('success', 'Procurement process started! Please send to CEO for approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to start procurement: ' . $e->getMessage());
        }
    }

   public function sendToCEO(Requisition $requisition)
{
    if ($requisition->status !== Requisition::STATUS_PROCUREMENT) {
        return back()->with('error', 'Only requisitions in procurement can be sent to CEO.');
    }

    \Log::info('Sending requisition to CEO:', [
        'requisition_id' => $requisition->id,
        'current_status' => $requisition->status
    ]);

    DB::beginTransaction();
    try {
        // Status remains as PROCUREMENT for CEO to see in pending approvals
        // CEO will change status to CEO_APPROVED when they approve

        // Create approval record to track sending to CEO
        RequisitionApproval::create([
            'requisition_id' => $requisition->id,
            'approved_by' => auth()->id(),
            'role' => 'procurement',
            'action' => 'sent_to_ceo',
            'comment' => 'Sent to CEO for final approval',
        ]);

        DB::commit();

        \Log::info('Requisition sent to CEO successfully:', ['requisition_id' => $requisition->id]);

        return redirect()->route('procurement.requisitions.show', $requisition)
            ->with('success', 'Requisition sent to CEO for approval! The CEO can now review and approve it.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to send to CEO:', [
            'requisition_id' => $requisition->id,
            'error' => $e->getMessage()
        ]);
        return back()->with('error', 'Failed to send to CEO: ' . $e->getMessage());
    }
}

 public function createLpo(Requisition $requisition)
{
    try {
        \Log::info('Starting LPO creation for requisition:', [
            'requisition_id' => $requisition->id,
            'current_status' => $requisition->status,
            'item_count' => $requisition->items->count()
        ]);

        $supplier_id = request('supplier_id');
        $delivery_date = request('delivery_date');
        $terms = request('terms');
        $notes = request('notes');
        
        if (!$supplier_id) {
            return back()->with('error', 'Supplier is required.');
        }

        if (!$delivery_date) {
            return back()->with('error', 'Delivery date is required.');
        }

        // Generate LPO number
        $lpoNumber = 'LPO-' . date('Ymd') . '-' . rand(1000, 9999);

        DB::beginTransaction();

        // Create LPO - REMOVE prepared_by
        $lpo = Lpo::create([
            'lpo_number' => $lpoNumber,
            'requisition_id' => $requisition->id,
            'supplier_id' => $supplier_id,
            'prepared_by' => auth()->id(),
            'status' => 'draft',
            'subtotal' => $requisition->estimated_total,
            'tax' => 0,
            'other_charges' => 0,
            'total' => $requisition->estimated_total,
            'delivery_date' => $delivery_date,
            'terms' => $terms,
            'notes' => $notes,
        ]);

        \Log::info('LPO created:', ['lpo_id' => $lpo->id, 'lpo_number' => $lpoNumber]);

        // Create LPO items
        $createdItems = 0;
        foreach ($requisition->items as $item) {
            \Log::info('Creating LPO item:', [
                'item_name' => $item->name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price
            ]);
            
            $lpoItem = LpoItem::create([
                'lpo_id' => $lpo->id,
                'inventory_item_id' => null,
                'description' => $item->name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
            
            if ($lpoItem) {
                $createdItems++;
                \Log::info('LPO item created successfully:', ['lpo_item_id' => $lpoItem->id]);
            } else {
                \Log::error('Failed to create LPO item for:', ['item_name' => $item->name]);
            }
        }

        \Log::info('LPO items creation summary:', [
            'attempted' => $requisition->items->count(),
            'created' => $createdItems
        ]);

        // Create approval record
        RequisitionApproval::create([
            'requisition_id' => $requisition->id,
            'approved_by' => auth()->id(),
            'role' => 'procurement',
            'action' => 'lpo_created_pending_ceo',
            'comment' => 'LPO created and ready for CEO approval: ' . $lpoNumber,
        ]);

        DB::commit();

        \Log::info('LPO creation completed successfully', [
            'requisition_id' => $requisition->id,
            'lpo_id' => $lpo->id,
            'items_created' => $createdItems
        ]);

        return redirect()->route('procurement.lpos.show', $lpo)
            ->with('success', 'LPO created successfully! The CEO can now review and approve it.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('LPO Creation Error: ' . $e->getMessage(), [
            'requisition_id' => $requisition->id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Failed to create LPO: ' . $e->getMessage());
    }
}


public function issueLpo(Lpo $lpo)
{
    // Only issue draft LPOs for CEO-approved requisitions
    if ($lpo->status !== 'draft') {
        abort(403, 'Only draft LPOs can be issued.');
    }

    if ($lpo->requisition->status !== Requisition::STATUS_CEO_APPROVED) {
        abort(403, 'Only CEO-approved requisitions can have LPOs issued to suppliers.');
    }

    DB::beginTransaction();
    try {
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

 public function edit(Requisition $requisition)
    {
        // Authorization - only allow editing of procurement and ceo_approved requisitions
        $allowedStatuses = [
            Requisition::STATUS_PROCUREMENT,
            Requisition::STATUS_CEO_APPROVED
        ];

        if (!in_array($requisition->status, $allowedStatuses)) {
            abort(403, 'Only requisitions in procurement or CEO-approved status can be edited.');
        }

        $requisition->load(['project', 'items', 'approvals', 'lpo']);
        $suppliers = Supplier::all();

        return view('procurement.requisitions.edit', compact('requisition', 'suppliers'));
    }

    public function update(Request $request, Requisition $requisition)
    {
        // Authorization - only allow editing of procurement and ceo_approved requisitions
        $allowedStatuses = [
            Requisition::STATUS_PROCUREMENT,
            Requisition::STATUS_CEO_APPROVED
        ];

        if (!in_array($requisition->status, $allowedStatuses)) {
            abort(403, 'Only requisitions in procurement or CEO-approved status can be edited.');
        }

        $validated = $request->validate([
            'urgency' => 'required|in:low,medium,high',
            'reason' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Store old values for audit trail
            $oldValues = [
                'urgency' => $requisition->urgency,
                'reason' => $requisition->reason,
                'estimated_total' => $requisition->estimated_total,
            ];

            // Calculate new total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }

            // Update requisition
            $requisition->update([
                'urgency' => $validated['urgency'],
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
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

            // Update LPO if it exists
            if ($requisition->lpo) {
                $requisition->lpo->update([
                    'subtotal' => $estimatedTotal,
                    'total' => $estimatedTotal,
                ]);

                // Update LPO items
                $requisition->lpo->items()->delete();
                foreach ($validated['items'] as $itemData) {
                    LpoItem::create([
                        'lpo_id' => $requisition->lpo->id,
                        'inventory_item_id' => null,
                        'description' => $itemData['name'],
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                    ]);
                }
            }

            // Create edit approval record for audit trail
            $changes = $this->getChangesDescription($oldValues, $requisition);
            
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'procurement',
                'action' => 'edited',
                'comment' => 'Requisition updated by Procurement: ' . $changes,
            ]);

            DB::commit();

            return redirect()->route('procurement.requisitions.show', $requisition)
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