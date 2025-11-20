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

      public function store(Request $request, $requisitionId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'tax_amount' => 'required|numeric|min:0',
            'vat_amount' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $requisition = Requisition::with(['lpo.supplier', 'lpo.receivedItems.lpoItem'])->findOrFail($requisitionId);

        if (!$requisition->lpo || !$requisition->lpo->supplier) {
            return back()->with('error', 'Cannot process payment: Supplier information missing for this requisition.');
        }

        // Calculate expected amounts based on actual received items
        $expectedBreakdown = $this->calculatePaymentBreakdown($requisition->lpo);
        
        $requestedAmount = $request->amount;
        $expectedTotal = $expectedBreakdown['total'];
        $difference = abs($requestedAmount - $expectedTotal);
        
        // Log significant differences
        if ($difference > 1000) {
            \Log::warning("Payment amount mismatch for LPO {$requisition->lpo->lpo_number}", [
                'requested' => $requestedAmount,
                'expected' => $expectedTotal,
                'difference' => $difference
            ]);
        }

        // Create payment with VAT and tax information
        $payment = Payment::create([
            'lpo_id' => $requisition->lpo->id,
            'supplier_id' => $requisition->lpo->supplier_id,
            'paid_by' => auth()->id(),
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'amount' => $request->amount,
            'paid_on' => $request->payment_date,
            'reference' => $request->reference_number,
            'notes' => $this->buildPaymentNotes($request, $expectedBreakdown, $difference),
            'tax_amount' => $request->tax_amount,
            'vat_amount' => $request->vat_amount,
        ]);

        // Update requisition status to completed
        $requisition->update(['status' => 'completed']);

        return redirect()->route('finance.payments.show', $payment)
            ->with('success', 'Payment processed successfully! VAT and tax amounts recorded.');
    }

     /**
     * Calculate payment breakdown based on received quantities and VAT
     */
    private function calculatePaymentBreakdown($lpo)
    {
        if (!$lpo || !$lpo->receivedItems) {
            return [
                'subtotal' => $lpo->subtotal ?? 0,
                'vat_amount' => $lpo->vat_amount ?? 0,
                'tax_amount' => 0,
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
            'tax_amount' => 0, // You can add tax calculation if needed
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
        $notes .= "\nTax: UGX " . number_format($request->tax_amount, 2);
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