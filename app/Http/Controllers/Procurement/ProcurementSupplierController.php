<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProcurementSupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return view('procurement.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('procurement.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'category' => 'required|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive'
        ]);

        Supplier::create($validated);

        return redirect()->route('procurement.suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function show(Supplier $supplier)
    {
        return view('procurement.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('procurement.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'category' => 'required|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive'
        ]);

        $supplier->update($validated);

        return redirect()->route('procurement.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has LPOs
        if ($supplier->lpos()->exists()) {
            return redirect()->route('procurement.suppliers.index')
                ->with('error', 'Cannot delete supplier with existing LPOs.');
        }

        $supplier->delete();

        return redirect()->route('procurement.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }
}