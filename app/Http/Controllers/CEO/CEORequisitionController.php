<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionApproval;
use App\Models\Lpo;
use App\Models\LpoItem;
use App\Models\Supplier;
use App\Exports\CEORequisitionsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CEORequisitionController extends Controller
{
    public function index(Request $request)
    {
        $query = Requisition::with(['project', 'requester', 'items', 'lpo', 'lpo.supplier']);

        // Apply status filter if provided
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Show all requisitions (not just specific statuses)
        $requisitions = $query->latest()->paginate(20);

        return view('ceo.requisitions.index', compact('requisitions'));
    }

    /**
 * Alias for approveRequisition - for route compatibility
 */
public function approve(Requisition $requisition)
{
    return $this->approveRequisition($requisition);
}

/**
 * Alias for rejectRequisition - for route compatibility
 */
public function reject(Requisition $requisition)
{
    return $this->rejectRequisition($requisition);
}

public function pending()
{
    // Show requisitions that need CEO approval (PROCUREMENT status with LPOs)
    $pendingRequisitions = Requisition::where('status', Requisition::STATUS_PROCUREMENT)
        ->whereHas('lpo', function($query) {
            $query->where('status', 'draft'); // Only show requisitions with draft LPOs
        })
        ->with(['project', 'requester', 'items', 'lpo', 'lpo.supplier', 'lpo.items'])
        ->latest()
        ->paginate(20);

    \Log::info('CEO Pending Requisitions:', [
        'count' => $pendingRequisitions->count(),
        'requisitions' => $pendingRequisitions->pluck('id')
    ]);

    return view('ceo.requisitions.pending', compact('pendingRequisitions'));
}

    public function show(Requisition $requisition)
{
    // CEO can view any requisition regardless of status
    $requisition->load([
        'project', 
        'requester', 
        'items', 
        'approvals.approver',
        'lpo.supplier',
        'lpo.items'
    ]);

    return view('ceo.requisitions.show', compact('requisition'));
}

public function approveRequisition(Requisition $requisition)
{
    // Only approve requisitions in procurement status with LPOs
    if ($requisition->status !== Requisition::STATUS_PROCUREMENT) {
        return back()->with('error', 'Only requisitions in procurement status can be approved by CEO.');
    }

    if (!$requisition->lpo) {
        return back()->with('error', 'This requisition does not have an LPO created yet.');
    }

    $validated = request()->validate([
        'comment' => 'nullable|string|max:500',
        'approved_items' => 'required|array',
        'approved_items.*' => 'boolean',
        'approved_quantities' => 'required|array',
        'approved_quantities.*' => 'numeric|min:0'
    ]);

    DB::beginTransaction();
    try {
        // Calculate approved total and track changes
        $approvedTotal = 0;
        $approvedItems = [];
        $modifications = [];

        // Update requisition items based on CEO approval
        foreach ($requisition->items as $item) {
            $itemId = $item->id;
            $isApproved = isset($validated['approved_items'][$itemId]) && $validated['approved_items'][$itemId];
            $approvedQuantity = $validated['approved_quantities'][$itemId] ?? 0;

            if ($isApproved && $approvedQuantity > 0) {
                // Item is approved - update quantity if changed
                $itemTotal = $approvedQuantity * $item->unit_price;
                $approvedTotal += $itemTotal;
                $approvedItems[] = $item->name;

                // Track modifications if quantity changed
                if ($approvedQuantity != $item->quantity) {
                    $modifications[] = "{$item->name}: {$item->quantity} → {$approvedQuantity} {$item->unit}";
                    
                    // Update requisition item quantity
                    $item->update([
                        'quantity' => $approvedQuantity,
                        'total_price' => $itemTotal
                    ]);
                } else {
                    // Quantity unchanged, just add to approved items
                    $approvedItems[] = $item->name;
                }
            } else {
                // Item is not approved or quantity is 0 - DELETE from requisition
                $modifications[] = "{$item->name}: REMOVED";
                $item->delete(); // Remove from requisition
            }
        }

        if ($approvedTotal == 0) {
            return back()->with('error', 'Cannot approve requisition with zero total amount.');
        }

        // Update requisition with approved amount
        $requisition->update([
            'status' => Requisition::STATUS_CEO_APPROVED,
            'estimated_total' => $approvedTotal // Update the total to reflect approved amount
        ]);

        // Update LPO with approved amounts
        $lpo = $requisition->lpo;
        $lpo->update([
            'subtotal' => $approvedTotal,
            'total' => $approvedTotal + $lpo->vat_amount, // Keep VAT calculation
        ]);

        // Update LPO items based on CEO approval
        foreach ($requisition->items as $item) {
            $itemId = $item->id;
            $isApproved = isset($validated['approved_items'][$itemId]) && $validated['approved_items'][$itemId];
            $approvedQuantity = $validated['approved_quantities'][$itemId] ?? 0;

            $lpoItem = $lpo->items()->where('description', $item->name)->first();
            
            if ($lpoItem) {
                if ($isApproved && $approvedQuantity > 0) {
                    // Update LPO item with approved quantity
                    $lpoItem->update([
                        'quantity' => $approvedQuantity,
                        'total_price' => $approvedQuantity * $item->unit_price,
                    ]);
                } else {
                    // Remove item from LPO
                    $lpoItem->delete();
                }
            }
        }

        // Remove LPO items that don't match any requisition items (for deleted items)
        $requisitionItemNames = $requisition->items->pluck('name')->toArray();
        $lpo->items()->whereNotIn('description', $requisitionItemNames)->delete();

        // Build approval comment
        $comment = $validated['comment'] ?? 'Approved by CEO';
        if (!empty($modifications)) {
            $comment .= " | Modifications: " . implode(', ', $modifications);
        }
        if (!empty($approvedItems)) {
            $comment .= " | Approved items: " . implode(', ', $approvedItems);
        }

        // Create approval record
        RequisitionApproval::create([
            'requisition_id' => $requisition->id,
            'approved_by' => auth()->id(),
            'role' => 'ceo',
            'action' => 'approved',
            'comment' => $comment,
            'approved_amount' => $approvedTotal,
        ]);

        DB::commit();

        return redirect()->route('ceo.requisitions.pending')
            ->with('success', 
                "Requisition approved successfully! " .
                count($approvedItems) . " items approved. " .
                "Total: UGX " . number_format($approvedTotal, 2)
            );

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('CEO Approval Error: ' . $e->getMessage());
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
        if ($requisition->status !== Requisition::STATUS_PROCUREMENT) {
            abort(403, 'Only procurement status requisitions can be rejected by CEO.');
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

        return view('ceo.requisitions.edit', compact('requisition', 'suppliers'));
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
                'role' => 'ceo',
                'action' => 'edited',
                'comment' => 'Requisition updated by CEO: ' . $changes,
            ]);

            DB::commit();

            return redirect()->route('ceo.requisitions.show', $requisition)
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

    /**
     * Export requisitions to Excel
     */
    public function exportExcel(Request $request)
    {
        $status = $request->get('status');
        return Excel::download(new CEORequisitionsExport($status), 'ceo_requisitions_' . date('Y-m-d') . '.xlsx');
    }
}