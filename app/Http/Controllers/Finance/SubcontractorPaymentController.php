<?php
// app/Http/Controllers/Finance/SubcontractorPaymentController.php - UPDATED
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ProjectSubcontractor;
use App\Models\SubcontractorPayment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubcontractorPaymentController extends Controller
{
    public function create(ProjectSubcontractor $projectSubcontractor)
    {
        $projectSubcontractor->load(['project', 'subcontractor', 'payments']);
        return view('finance.subcontractors.payments.create', compact('projectSubcontractor'));
    }

    public function store(Request $request, ProjectSubcontractor $projectSubcontractor)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $projectSubcontractor->balance,
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:advance,progress,final,retention',
            'description' => 'required|string|max:500',
            'payment_method' => 'required|in:bank_transfer,cash,cheque,mobile_money',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $projectSubcontractor) {
            $payment = SubcontractorPayment::create([
                'project_subcontractor_id' => $projectSubcontractor->id,
                'payment_reference' => 'SUB-PAY-' . date('Ymd') . '-' . rand(1000, 9999),
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_type' => $request->payment_type,
                'description' => $request->description,
                'paid_by' => auth()->id(),
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            // Automatically create expense record
            Expense::create([
                'project_id' => $projectSubcontractor->project_id,
                'type' => 'subcontractor',
                'description' => 'Subcontractor Payment: ' . $request->description . ' - ' . $projectSubcontractor->subcontractor->name,
                'amount' => $request->amount,
                'incurred_on' => $request->payment_date,
                'recorded_by' => auth()->id(),
                'status' => 'paid',
                'notes' => $request->notes . " | Payment Ref: " . $payment->payment_reference,
                'reference_id' => $payment->id,
                'reference_type' => SubcontractorPayment::class,
            ]);

            // Check if contract is completed
            if ($projectSubcontractor->balance == 0) {
                $projectSubcontractor->update(['status' => 'completed']);
            }
        });

        return redirect()->route('finance.subcontractors.ledger', $projectSubcontractor)
            ->with('success', 'Payment recorded successfully!');
    }
}