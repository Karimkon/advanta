<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\Procurement;
use Illuminate\Http\Request;

class AdminLpoController extends Controller
{
    public function index()
    {
        return view('admin.lpos.index', [
            'lpos' => Lpo::with(['procurement.requisition.project'])->latest()->get(),
        ]);
    }

    public function create(Procurement $procurement)
    {
        return view('admin.lpos.create', compact('procurement'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'procurement_id' => 'required|exists:procurements,id',
            'lpo_number' => 'required|unique:lpos',
            'issue_date' => 'required|date',
            'status' => 'required'
        ]);

        Lpo::create($request->all());

        return redirect()->route('admin.lpos.index')
            ->with('success', 'LPO Issued');
    }
}
