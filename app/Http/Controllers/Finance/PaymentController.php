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
            'lpo.receivedItems' // Load received items
        ])->findOrFail($requisitionId);

        // Calculate actual amount based on received quantities
        $actualAmount = $this->calculateActualAmount($requisition->lpo);

        return view('finance.payments.create', compact('requisition', 'actualAmount'));
    }

    public function store(Request $request, $requisitionId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'tax_amount' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $requisition = Requisition::with(['lpo.supplier', 'lpo.receivedItems'])->findOrFail($requisitionId);

        // Debug: Check if supplier exists
        if (!$requisition->lpo || !$requisition->lpo->supplier) {
            return back()->with('error', 'Cannot process payment: Supplier information missing for this requisition.');
        }

        // Calculate actual amount based on received quantities
        $calculatedAmount = $this->calculateActualAmount($requisition->lpo);
        
        // Warn if payment amount differs significantly from actual received amount
        $requestedAmount = $request->amount;
        $difference = abs($requestedAmount - $calculatedAmount);
        
        if ($difference > 1000) { // Allow small rounding differences
            \Log::warning("Payment amount mismatch for LPO {$requisition->lpo->lpo_number}: Requested UGX {$requestedAmount}, Calculated UGX {$calculatedAmount}");
        }

        // Create payment linked to LPO and Supplier
        $payment = Payment::create([
            'lpo_id' => $requisition->lpo->id,
            'supplier_id' => $requisition->lpo->supplier_id,
            'paid_by' => auth()->id(),
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'amount' => $request->amount,
            'paid_on' => $request->payment_date,
            'reference' => $request->reference_number,
            'notes' => $request->notes . ($difference > 1000 ? " [Note: Amount differs from received goods value]" : ""),
            'tax_amount' => $request->tax_amount,
            'other_charges' => $request->other_charges ?? 0,
        ]);

        // Update requisition status to completed
        $requisition->update(['status' => 'completed']);

        return redirect()->route('finance.payments.index')
            ->with('success', 'Payment processed successfully!');
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
     * Calculate actual payment amount based on received quantities
     */
    private function calculateActualAmount($lpo)
    {
        if (!$lpo || !$lpo->receivedItems) {
            return $lpo->total ?? 0;
        }

        $actualAmount = 0;
        
        foreach ($lpo->receivedItems as $receivedItem) {
            if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0) {
                $actualAmount += $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
            }
        }

        return $actualAmount;
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