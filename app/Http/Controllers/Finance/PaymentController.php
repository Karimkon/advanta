<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\Lpo;
use App\Models\LpoReceivedItem;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['lpo.requisition.project', 'supplier', 'paidBy'])
            ->latest()
            ->paginate(20);

        return view('finance.payments.index', compact('payments'));
    }

    public function pending()
    {
        $pendingRequisitions = Requisition::where('status', 'delivered')
            ->whereHas('lpo', function($query) {
                $query->where('status', 'delivered');
            })
            ->with(['project', 'lpo.supplier', 'lpo', 'lpo.receivedItems', 'items'])
            ->latest()
            ->paginate(15);

        return view('finance.payments.pending', compact('pendingRequisitions'));
    }

  public function create($requisitionId)
    {
        $requisition = Requisition::with([
            'project', 
            'lpo.supplier', 
            'lpo', 
            'lpo.items',
            'lpo.receivedItems.lpoItem'
        ])->findOrFail($requisitionId);

        // Calculate actual amounts based on received quantities
        $breakdown = $this->calculatePaymentBreakdown($requisition->lpo);
        
        return view('finance.payments.create', compact('requisition', 'breakdown'));
    }

public function store(Request $request, Requisition $requisition)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:0',
        'payment_method' => 'required|string',
        'paid_on' => 'required|date',
        'reference' => 'required|string|max:255',
        'notes' => 'nullable|string',
        'vat_amount' => 'nullable|numeric|min:0',
        'additional_costs' => 'nullable|numeric|min:0',
        'additional_costs_description' => 'nullable|string|max:500'
    ]);

    DB::transaction(function () use ($validated, $requisition) {
        $payment = Payment::create([
            'lpo_id' => $requisition->lpo->id,
            'supplier_id' => $requisition->lpo->supplier_id,
            'paid_by' => auth()->id(),
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['amount'],
            'paid_on' => $validated['paid_on'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'vat_amount' => $validated['vat_amount'] ?? 0,
            'additional_costs' => $validated['additional_costs'] ?? 0,
            'status' => 'pending_ceo', // NEW: Send to CEO for approval
            'approval_status' => 'pending_ceo' // NEW
        ]);

        // Update requisition status
        $requisition->update(['status' => Requisition::STATUS_PAYMENT_PENDING_CEO]);
    });

    return redirect()->route('finance.payments.index')
        ->with('success', 'Payment created and sent to CEO for approval!');
}

    /**
     * Calculate payment breakdown based on received quantities
     */
    private function calculatePaymentBreakdown($lpo)
    {
        if (!$lpo || !$lpo->receivedItems) {
            return [
                'subtotal' => $lpo->subtotal ?? 0,
                'vat_amount' => $lpo->vat_amount ?? 0,
                'additional_costs' => 0,
                'total' => $lpo->total ?? 0,
            ];
        }

        $subtotal = 0;
        $vatAmount = 0;
        
        foreach ($lpo->receivedItems as $receivedItem) {
            if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0) {
                $itemTotal = $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
                $subtotal += $itemTotal;
                
                // Add VAT if applicable
                if ($receivedItem->lpoItem->has_vat) {
                    $vatAmount += $itemTotal * ($receivedItem->lpoItem->vat_rate / 100);
                }
            }
        }

        $total = $subtotal + $vatAmount;

        return [
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'additional_costs' => 0, // Additional costs are manually entered
            'total' => $total,
            'vat_percentage' => $subtotal > 0 ? ($vatAmount / $subtotal) * 100 : 0,
        ];
    }


     /**
     * Build comprehensive payment notes
     */
    private function buildPaymentNotes($request, $expectedBreakdown, $difference)
    {
        $notes = $request->notes ?? '';
        
        $notes .= "\n\n--- PAYMENT BREAKDOWN ---";
        $notes .= "\nSubtotal: UGX " . number_format($expectedBreakdown['subtotal'], 2);
        $notes .= "\nVAT (" . number_format($expectedBreakdown['vat_percentage'], 1) . "%): UGX " . number_format($expectedBreakdown['vat_amount'], 2);
        
        // Add additional costs if any
        if ($request->additional_costs > 0) {
            $description = $request->additional_costs_description ?: 'Additional Costs';
            $notes .= "\n{$description}: UGX " . number_format($request->additional_costs, 2);
        }
        
        $notes .= "\nTotal: UGX " . number_format($request->amount, 2);
        
        if ($difference > 1000) {
            $notes .= "\n\n⚠️ NOTE: Payment amount differs from expected value by UGX " . number_format($difference, 2);
        }
        
        return $notes;
    }



    public function show($id)
    {
        $payment = Payment::with([
            'lpo.requisition.project', 
            'supplier', 
            'paidBy',
            'lpo.requisition.items',
            'lpo.receivedItems.lpoItem'
        ])->findOrFail($id);

        return view('finance.payments.show', compact('payment'));
    }

    /**
     * Calculate actual payment amount based on received quantities INCLUDING VAT
     */
    private function calculateActualAmount($lpo)
    {
        if (!$lpo || !$lpo->receivedItems) {
            return $lpo->total ?? 0;
        }

        $actualAmount = 0;
        
        foreach ($lpo->receivedItems as $receivedItem) {
            if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0) {
                $itemTotal = $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
                
                // Add VAT if applicable
                if ($receivedItem->lpoItem->has_vat) {
                    $itemTotal += $itemTotal * ($receivedItem->lpoItem->vat_rate / 100);
                }
                
                $actualAmount += $itemTotal;
            }
        }

        return $actualAmount;
    }

     /**
     * Calculate VAT amount based on received quantities
     */
    private function calculateVatAmount($lpo)
    {
        if (!$lpo || !$lpo->receivedItems) {
            return $lpo->vat_amount ?? 0;
        }

        $vatAmount = 0;
        
        foreach ($lpo->receivedItems as $receivedItem) {
            if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0 && $receivedItem->lpoItem->has_vat) {
                $itemTotal = $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
                $vatAmount += $itemTotal * ($receivedItem->lpoItem->vat_rate / 100);
            }
        }

        return $vatAmount;
    }

    /**
     * Export payments to CSV
     */
    public function export()
    {
        $payments = Payment::with(['lpo.requisition.project', 'supplier', 'paidBy'])
            ->whereNotNull('paid_on')
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($payments) {
            $handle = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($handle, [
                'Payment ID',
                'Payment Date', 
                'Supplier',
                'Payment Method',
                'Amount',
                'Status',
                'Reference',
                'Project',
                'LPO Number',
                'Processed By'
            ]);

            // Add data
            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->id,
                    $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                    $payment->supplier->name ?? 'N/A',
                    $payment->payment_method,
                    $payment->amount,
                    $payment->status,
                    $payment->reference ?? 'N/A',
                    $payment->lpo->requisition->project->name ?? 'N/A',
                    $payment->lpo->lpo_number ?? 'N/A',
                    $payment->paidBy->name ?? 'N/A'
                ]);
            }

            fclose($handle);
        }, 'payments_export_' . date('Y-m-d') . '.csv');
    }
}