<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfficeStaff;
use Illuminate\Support\Facades\Auth;

class OfficeStaffController extends Controller
{
    public function index()
    {
        $staff = OfficeStaff::latest()->get();
        // Determine layout and route prefix based on user role
        $layout = 'finance.layouts.app';
        $routePrefix = 'finance.';
        if (Auth::user()->role === 'admin') {
            $layout = 'admin.layouts.app';
            $routePrefix = 'admin.';
        }
        if (Auth::user()->role === 'ceo') {
            $layout = 'ceo.layouts.app';
            $routePrefix = 'ceo.';
        }
        
        return view('finance.office_staff.index', compact('staff', 'layout', 'routePrefix'));
    }

    public function create()
    {
        $layout = 'finance.layouts.app';
        $routePrefix = 'finance.';
        if (Auth::user()->role === 'admin') {
            $layout = 'admin.layouts.app';
            $routePrefix = 'admin.';
        }
        if (Auth::user()->role === 'ceo') {
            $layout = 'ceo.layouts.app';
            $routePrefix = 'ceo.';
        }
        return view('finance.office_staff.create', compact('layout', 'routePrefix'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'salary' => 'required|numeric|min:0',
            'joined_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);

        OfficeStaff::create([
            'name' => $request->name,
            'role' => $request->role,
            'department' => $request->department,
            'phone' => $request->phone,
            'email' => $request->email,
            'salary' => $request->salary,
            'joined_date' => $request->joined_date,
            'status' => $request->status,
            'created_by' => Auth::id(),
        ]);

        $routePrefix = Auth::user()->role === 'admin' ? 'admin.' : (Auth::user()->role === 'ceo' ? 'ceo.' : 'finance.');
        return redirect()->route($routePrefix . 'office-staff.index')
            ->with('success', 'Office staff member added successfully.');
    }

    public function show(OfficeStaff $officeStaff)
    {
        $officeStaff->load('payments');
        $layout = 'finance.layouts.app';
        $routePrefix = 'finance.';
        if (Auth::user()->role === 'admin') {
            $layout = 'admin.layouts.app';
            $routePrefix = 'admin.';
        }
        if (Auth::user()->role === 'ceo') {
            $layout = 'ceo.layouts.app';
            $routePrefix = 'ceo.';
        }
        return view('finance.office_staff.show', compact('officeStaff', 'layout', 'routePrefix'));
    }

    public function edit(OfficeStaff $officeStaff)
    {
        $layout = 'finance.layouts.app';
        $routePrefix = 'finance.';
        if (Auth::user()->role === 'admin') {
            $layout = 'admin.layouts.app';
            $routePrefix = 'admin.';
        }
        if (Auth::user()->role === 'ceo') {
            $layout = 'ceo.layouts.app';
            $routePrefix = 'ceo.';
        }
        return view('finance.office_staff.edit', compact('officeStaff', 'layout', 'routePrefix'));
    }

    public function createPayment(OfficeStaff $officeStaff)
    {
        $layout = 'finance.layouts.app';
        $routePrefix = 'finance.';
        if (Auth::user()->role === 'admin') {
            $layout = 'admin.layouts.app';
            $routePrefix = 'admin.';
        }
        if (Auth::user()->role === 'ceo') {
            $layout = 'ceo.layouts.app';
            $routePrefix = 'ceo.';
        }
        return view('finance.office_staff.create_payment', compact('officeStaff', 'layout', 'routePrefix'));
    }

    public function update(Request $request, OfficeStaff $officeStaff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'salary' => 'required|numeric|min:0',
            'joined_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);

        $officeStaff->update($request->all());

        $routePrefix = Auth::user()->role === 'admin' ? 'admin.' : (Auth::user()->role === 'ceo' ? 'ceo.' : 'finance.');
        return redirect()->route($routePrefix . 'office-staff.index')
            ->with('success', 'Office staff details updated successfully.');
    }

    public function destroy(OfficeStaff $officeStaff)
    {
        $officeStaff->delete();
        $routePrefix = Auth::user()->role === 'admin' ? 'admin.' : (Auth::user()->role === 'ceo' ? 'ceo.' : 'finance.');
        return redirect()->route($routePrefix . 'office-staff.index')
            ->with('success', 'Office staff member deleted successfully.');
    }
}
