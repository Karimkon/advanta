<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Models\Requisition;
use Illuminate\Http\Request;

class AdminProcurementController extends Controller
{
    public function index()
    {
        return view('admin.procurement.index', [
            'items' => Procurement::with(['requisition.project'])->latest()->get(),
        ]);
    }

    public function create(Requisition $requisition)
    {
        return view('admin.procurement.create', compact('requisition'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'requisition_id' => 'required|exists:requisitions,id',
            'supplier_name' => 'required',
            'supplier_contact' => 'nullable',
            'evaluated_cost' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $proc = Procurement::create($request->all());

        // update requisition status
        $proc->requisition->update(['status' => 'procurement_reviewed']);

        return redirect()->route('admin.procurement.index')
            ->with('success', 'Procurement details saved');
    }
}
