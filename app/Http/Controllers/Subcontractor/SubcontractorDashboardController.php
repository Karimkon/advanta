<?php

namespace App\Http\Controllers\Subcontractor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubcontractorDashboardController extends Controller
{
    /**
     * Show subcontractor dashboard
     */
    public function index()
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        // Get active project contracts
        $activeContracts = $subcontractor->projectSubcontractors()
            ->where('status', 'active')
            ->with(['project'])
            ->get();

        // Get recent requisitions
        $recentRequisitions = $subcontractor->requisitions()
            ->with(['project', 'items'])
            ->latest()
            ->take(10)
            ->get();

        // Get statistics
        $stats = [
            'total_contracts' => $subcontractor->projectSubcontractors()->count(),
            'active_contracts' => $activeContracts->count(),
            'total_requisitions' => $subcontractor->requisitions()->count(),
            'pending_requisitions' => $subcontractor->requisitions()->where('status', 'pending')->count(),
            'total_contract_value' => $subcontractor->total_contracts_amount,
            'total_paid' => $subcontractor->total_paid_amount,
            'balance' => $subcontractor->balance,
        ];

        return view('subcontractor.dashboard', compact(
            'subcontractor',
            'activeContracts',
            'recentRequisitions',
            'stats'
        ));
    }
}
