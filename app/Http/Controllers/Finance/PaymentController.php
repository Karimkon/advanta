<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\Lpo;
use App\Models\Expense;
use App\Models\LpoReceivedItem;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
{
    $query = Payment::with(['lpo.requisition.project', 'supplier', 'paidBy'])
        ->whereNotNull('paid_on');
    
    // Apply filters
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('date_from')) {
        $query->whereDate('paid_on', '>=', $request->date_from);
    }
    
    if ($request->filled('date_to')) {
        $query->whereDate('paid_on', '<=', $request->date_to);
    }
    
    $payments = $query->latest()->paginate(20);
    
    // Calculate totals for the view
    $totalAmount = $payments->sum('amount');
    $totalVat = $payments->sum('vat_amount');
    $totalBaseAmount = $totalAmount - $totalVat;

    return view('finance.payments.index', compact('payments', 'totalAmount', 'totalVat', 'totalBaseAmount'));
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
    $request->validate([
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:bank_transfer,cash,cheque,mobile_money',
        'payment_date' => 'required|date',
        'vat_amount' => 'required|numeric|min:0',
        'additional_costs' => 'nullable|numeric|min:0',
        'additional_costs_description' => 'nullable|string|max:255',
        'reference_number' => 'nullable|string|max:100',
        'notes' => 'nullable|string|max:1000',
    ]);

    try {
        DB::transaction(function () use ($request, $requisition) {
            // Create payment record - SET AS PENDING CEO APPROVAL
            $payment = Payment::create([
                'lpo_id' => $requisition->lpo->id,
                'supplier_id' => $requisition->supplier_id ?? $requisition->lpo->supplier_id ?? null,
                'paid_by' => auth()->id(),
                'payment_method' => $request->payment_method,
                'status' => 'pending', // Payment is pending until CEO approves
                'amount' => $request->amount,
                'paid_on' => $request->payment_date,
                'reference' => $request->reference_number,
                'notes' => $request->notes,
                'vat_amount' => $request->vat_amount,
                'additional_costs' => $request->additional_costs ?? 0,
                'additional_costs_description' => $request->additional_costs_description,
                'approval_status' => Payment::APPROVAL_PENDING, // Set as pending CEO approval
            ]);

            // Update requisition status - USE THE CORRECT CONSTANT
            $requisition->update([
                'status' => Requisition::STATUS_PAYMENT_COMPLETED // Changed from STATUS_PAYMENT_PROCESSED
            ]);

            // Create expense record
            Expense::create([
                'project_id' => $requisition->project_id,
                'type' => 'supplier_payment',
                'description' => 'Supplier Payment: ' . $requisition->supplier->name . ' - ' . $requisition->ref,
                'amount' => $request->amount,
                'incurred_on' => $request->payment_date,
                'recorded_by' => auth()->id(),
                'status' => 'pending', // Expense also pending until payment is approved
                'notes' => $request->notes . " | Payment Ref: " . ($request->reference_number ?? 'N/A'),
                'reference_id' => $payment->id,
                'reference_type' => Payment::class,
            ]);
        });

        return redirect()->route('finance.payments.pending')
            ->with('success', 'Payment processed successfully! It is now pending CEO approval.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error processing payment: ' . $e->getMessage())
            ->withInput();
    }
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
        
        // Add UTF-8 BOM for Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($handle, [
            'Payment ID',
            'Payment Date', 
            'Supplier',
            'Payment Method',
            'Base Amount',
            'VAT Amount',
            'Total Amount',
            'Status',
            'VAT %',
            'Reference',
            'Project',
            'LPO Number',
            'Processed By'
        ]);

        // Add data
        foreach ($payments as $payment) {
            $baseAmount = $payment->amount - $payment->vat_amount;
            $vatPercentage = $baseAmount > 0 ? ($payment->vat_amount / $baseAmount) * 100 : 0;
            
            fputcsv($handle, [
                $payment->id,
                $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                $payment->supplier->name ?? 'N/A',
                $payment->payment_method,
                number_format($baseAmount, 2),
                number_format($payment->vat_amount, 2),
                number_format($payment->amount, 2),
                $payment->status,
                round($vatPercentage, 1) . '%',
                $payment->reference ?? 'N/A',
                $payment->lpo->requisition->project->name ?? 'N/A',
                $payment->lpo->lpo_number ?? 'N/A',
                $payment->paidBy->name ?? 'N/A'
            ]);
        }

        fclose($handle);
    }, 'payments_with_vat_export_' . date('Y-m-d') . '.csv');
}
}