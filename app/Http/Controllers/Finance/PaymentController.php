<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\Lpo;

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
        ->with(['project', 'lpo.supplier', 'lpo', 'items'])
        ->latest()
        ->paginate(15);

    return view('finance.payments.pending', compact('pendingRequisitions'));
}

public function create($requisitionId)
{
    $requisition = Requisition::with(['project', 'lpo.supplier', 'lpo', 'items'])
        ->findOrFail($requisitionId);

    return view('finance.payments.create', compact('requisition'));
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

    $requisition = Requisition::with('lpo.supplier')->findOrFail($requisitionId);

    // Debug: Check if supplier exists
    if (!$requisition->lpo || !$requisition->lpo->supplier) {
        return back()->with('error', 'Cannot process payment: Supplier information missing for this requisition.');
    }

    // Create payment linked to LPO and Supplier
    $payment = Payment::create([
        'lpo_id' => $requisition->lpo->id,
        'supplier_id' => $requisition->lpo->supplier_id, // Ensure supplier_id is set
        'paid_by' => auth()->id(),
        'payment_method' => $request->payment_method,
        'status' => 'completed',
        'amount' => $request->amount,
        'paid_on' => $request->payment_date, // Make sure this is set
        'reference' => $request->reference_number,
        'notes' => $request->notes,
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
        'lpo.requisition.items'
    ])->findOrFail($id);

    return view('finance.payments.show', compact('payment'));
}
}