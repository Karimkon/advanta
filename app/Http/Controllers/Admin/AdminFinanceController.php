<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use App\Models\Lpo;
use Illuminate\Http\Request;

class AdminFinanceController extends Controller
{
    public function index()
    {
        return view('admin.finance.index', [
            'records' => Finance::with(['lpo.procurement.requisition.project'])->latest()->get(),
        ]);
    }

    public function create(Lpo $lpo)
    {
        return view('admin.finance.create', compact('lpo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lpo_id' => 'required|exists:lpos,id',
            'amount_paid' => 'required|numeric',
            'payment_date' => 'required|date',
            'reference_number' => 'required',
        ]);

        Finance::create($request->all());

        return redirect()->route('admin.finance.index')
            ->with('success', 'Payment recorded');
    }
}
