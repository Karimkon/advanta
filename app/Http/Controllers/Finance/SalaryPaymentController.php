<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalaryPayment;
use App\Models\OfficeStaff;
use Illuminate\Support\Facades\Auth;

class SalaryPaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'office_staff_id' => 'required|exists:office_staff,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'month_for' => 'required|string',
            'payment_method' => 'nullable|string',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        SalaryPayment::create([
            'office_staff_id' => $request->office_staff_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'month_for' => $request->month_for,
            'payment_method' => $request->payment_method,
            'reference' => $request->reference,
            'notes' => $request->notes,
            'status' => 'paid',
            'paid_by' => Auth::id(),
        ]);

        return redirect()->route('finance.office-staff.show', $request->office_staff_id)->with('success', 'Salary payment recorded successfully.');
    }

    public function destroy(SalaryPayment $payment)
    {
        $payment->delete();
        return redirect()->back()->with('success', 'Payment record deleted successfully.');
    }
}
