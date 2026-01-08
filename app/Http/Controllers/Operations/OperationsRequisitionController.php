<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RequisitionItem; 

class OperationsRequisitionController extends Controller
{
    public function index()
{
    // Operations should see all requisitions that are relevant to them
    $requisitions = Requisition::whereIn('status', [
        Requisition::STATUS_PROJECT_MANAGER_APPROVED,  // Waiting for operations approval
        Requisition::STATUS_OPERATIONS_APPROVED,       // Approved by operations
        Requisition::STATUS_PROCUREMENT,               // Sent to procurement
        Requisition::STATUS_CEO_APPROVED,              // CEO approved (optional)
        Requisition::STATUS_LPO_ISSUED,                // LPO issued (optional)
        Requisition::STATUS_DELIVERED                  // Delivered (optional)
    ])
    ->with(['project', 'requester', 'items', 'approvals' => function($query) {
        $query->where('role', 'operations'); // Show operations approvals
    }])
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
        // Get requisitions where operations role has approved them
        $approvedRequisitions = Requisition::whereHas('approvals', function($query) {
                $query->where('role', 'operations')
                      ->where('action', 'approved');
            })
            ->with(['project', 'requester', 'items', 'approvals' => function($query) {
                $query->where('role', 'operations');
            }])
            ->latest()
            ->paginate(10);

        return view('operations.requisitions.approved', compact('approvedRequisitions'));
    }

    public function show(Requisition $requisition)
    {
        // FIXED: Allow Operations to view requisitions they can approve OR have already approved
        $allowedStatuses = [
            Requisition::STATUS_PROJECT_MANAGER_APPROVED, // Can approve these
            Requisition::STATUS_OPERATIONS_APPROVED,      // Already approved these
            Requisition::STATUS_PROCUREMENT               // Sent to procurement
        ];

        if (!in_array($requisition->status, $allowedStatuses)) {
            abort(403, 'This requisition is not available for operations view.');
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
        // Authorization - only approve requisitions in project_manager_approved status
        if ($requisition->status !== Requisition::STATUS_PROJECT_MANAGER_APPROVED) {
            abort(403, 'Only project-manager-approved requisitions can be approved by operations.');
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
        // Authorization - only reject requisitions in project_manager_approved status
        if ($requisition->status !== Requisition::STATUS_PROJECT_MANAGER_APPROVED) {
            abort(403, 'Only project-manager-approved requisitions can be rejected by operations.');
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

      public function edit(Requisition $requisition)
    {
        // Authorization - only allow editing of project_manager_approved requisitions
        if ($requisition->status !== Requisition::STATUS_PROJECT_MANAGER_APPROVED) {
            abort(403, 'Only project-manager-approved requisitions can be edited by operations.');
        }

        $requisition->load(['project', 'items', 'approvals']);

        return view('operations.requisitions.edit', compact('requisition'));
    }

    public function update(Request $request, Requisition $requisition)
    {
        // Authorization - only allow editing of project_manager_approved requisitions
        if ($requisition->status !== Requisition::STATUS_PROJECT_MANAGER_APPROVED) {
            abort(403, 'Only project-manager-approved requisitions can be edited by operations.');
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

            // Create edit approval record for audit trail
            $changes = $this->getChangesDescription($oldValues, $requisition);
            
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'operations',
                'action' => 'edited',
                'comment' => 'Requisition updated by Operations: ' . $changes,
            ]);

            DB::commit();

            return redirect()->route('operations.requisitions.show', $requisition)
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