<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\Expense;
use App\Exports\CEOPaymentsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CEOPaymentController extends Controller
{
    public function pendingPayments()
    {
        $pendingPayments = Payment::pendingCeoApproval()
            ->with([
                'supplier' => function($query) {
                    $query->withDefault(['name' => 'Unknown Supplier']);
                },
                'lpo.requisition.project' => function($query) {
                    $query->withDefault(['name' => 'Unknown Project']);
                },
                'paidBy' => function($query) {
                    $query->withDefault(['name' => 'Unknown User']);
                }
            ])
            ->latest()
            ->paginate(10);

        return view('ceo.payments.pending', compact('pendingPayments'));
    }

    public function showPayment(Payment $payment)
    {
        $payment->load([
            'supplier' => function($query) {
                $query->withDefault(['name' => 'Unknown Supplier']);
            },
            'lpo.requisition.project' => function($query) {
                $query->withDefault(['name' => 'Unknown Project']);
            },
            'paidBy' => function($query) {
                $query->withDefault(['name' => 'Unknown User']);
            },
            'ceoApprovedBy' => function($query) {
                $query->withDefault(['name' => 'N/A']);
            }
        ]);
        
        return view('ceo.payments.show', compact('payment'));
    }

    public function approvePayment(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_voucher' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'ceo_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($request, $payment) {
                $voucherPath = null;
                if ($request->hasFile('payment_voucher')) {
                    $voucherPath = $request->file('payment_voucher')->store('payment-vouchers', 'public');
                }

                $payment->update([
                    'approval_status' => Payment::APPROVAL_APPROVED,
                    'ceo_approved' => true,
                    'ceo_approved_by' => auth()->id(),
                    'ceo_approved_at' => now(),
                    'ceo_notes' => $request->ceo_notes,
                    'payment_voucher_path' => $voucherPath,
                    'status' => 'completed', // Mark payment as completed
                ]);

                // Update related expense status if exists
                $expense = Expense::where('reference_id', $payment->id)
                    ->where('reference_type', Payment::class)
                    ->first();
                    
                if ($expense) {
                    $expense->update(['status' => 'approved']);
                }
            });

            return redirect()->route('ceo.payments.pending')
                ->with('success', 'Payment approved successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving payment: ' . $e->getMessage());
        }
    }

   public function rejectPayment(Request $request, Payment $payment)
{
    $request->validate([
        'ceo_notes' => 'required|string|max:1000',
    ]);

    try {
        DB::transaction(function () use ($request, $payment) {
            // Store rejection reason
            $rejectionNotes = $request->ceo_notes;
            
            // Reset requisition status FIRST
            if ($payment->lpo && $payment->lpo->requisition) {
                $payment->lpo->requisition->update([
                    'status' => Requisition::STATUS_DELIVERED
                ]);
            }
            
            // Delete the payment and expense
            Expense::where('reference_id', $payment->id)
                ->where('reference_type', Payment::class)
                ->delete();
                
            $payment->delete();
            
            // Store rejection in audit log or separate table
            \App\Models\PaymentRejection::create([
                'payment_id' => $payment->id,
                'reason' => $rejectionNotes,
                'rejected_by' => auth()->id(),
            ]);
        });

        return redirect()->route('ceo.payments.pending')
            ->with('success', 'Payment rejected and removed. Finance can create a new payment.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error rejecting payment: ' . $e->getMessage());
    }
}


    public function allPayments()
    {
        $payments = Payment::with(['supplier', 'lpo.requisition.project'])
            ->latest()
            ->paginate(15);

        return view('ceo.payments.index', compact('payments'));
    }

    /**
     * Export payments to Excel
     */
    public function exportExcel(Request $request)
    {
        $approvalStatus = $request->get('approval_status');
        return Excel::download(new CEOPaymentsExport($approvalStatus), 'ceo_payments_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export payments to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Payment::with(['lpo.requisition.project', 'supplier', 'paidBy', 'approvedBy']);

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        $payments = $query->latest()->get();

        $pdf = Pdf::loadView('exports.pdf.payments', [
            'payments' => $payments,
            'title' => 'CEO Payments Report'
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('ceo_payments_' . date('Y-m-d') . '.pdf');
    }
}