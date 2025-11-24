<?php
// app/Http/Controllers/CEO/CEOPaymentController.php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CEOPaymentController extends Controller
{
    public function pendingPayments()
    {
        $pendingPayments = Payment::where('approval_status', 'pending_ceo')
            ->with(['supplier', 'lpo.requisition.project', 'paidBy'])
            ->latest()
            ->paginate(10);

        $pendingCount = Payment::where('approval_status', 'pending_ceo')->count();

        return view('ceo.payments.pending', compact('pendingPayments', 'pendingCount'));
    }

    public function showPayment(Payment $payment)
    {
        $payment->load(['supplier', 'lpo.requisition.project', 'paidBy', 'lpo.items']);
        
        return view('ceo.payments.show', compact('payment'));
    }

    public function approvePayment(Request $request, Payment $payment)
    {
        $request->validate([
            'ceo_notes' => 'nullable|string|max:1000',
            'payment_voucher' => 'nullable|file|mimes:pdf,jpg,png|max:2048'
        ]);

        DB::transaction(function () use ($request, $payment) {
            $payment->update([
                'ceo_approved' => true,
                'ceo_approved_by' => auth()->id(),
                'ceo_approved_at' => now(),
                'ceo_notes' => $request->ceo_notes,
                'approval_status' => 'ceo_approved'
            ]);

            // Handle voucher upload
            if ($request->hasFile('payment_voucher')) {
                $voucherPath = $request->file('payment_voucher')->store('payment-vouchers', 'public');
                $payment->update(['payment_voucher_path' => $voucherPath]);
            }
        });

        return redirect()->route('ceo.payments.pending')
            ->with('success', 'Payment approved successfully!');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'ceo_notes' => 'required|string|max:1000'
        ]);

        $payment->update([
            'ceo_approved' => false,
            'ceo_approved_by' => auth()->id(),
            'ceo_approved_at' => now(),
            'ceo_notes' => $request->ceo_notes,
            'approval_status' => 'ceo_rejected'
        ]);

        return redirect()->route('ceo.payments.pending')
            ->with('success', 'Payment rejected successfully!');
    }

    public function allPayments()
    {
        $payments = Payment::with(['supplier', 'lpo.requisition.project'])
            ->latest()
            ->paginate(15);

        return view('ceo.payments.index', compact('payments'));
    }
}