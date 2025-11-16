<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Lpo;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementDashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    
    // Get requisitions pending procurement action
    $pendingRequisitions = Requisition::where('status', Requisition::STATUS_OPERATIONS_APPROVED)
        ->with(['project', 'requester', 'items'])
        ->latest()
        ->take(5)
        ->get();

    // Get requisitions in procurement
    $procurementRequisitions = Requisition::where('status', Requisition::STATUS_PROCUREMENT)
        ->with(['project', 'requester'])
        ->latest()
        ->take(5)
        ->get();

    // Get statistics
    $requisitionStats = DB::table('requisitions')
        ->whereIn('status', [
            Requisition::STATUS_OPERATIONS_APPROVED,
            Requisition::STATUS_PROCUREMENT,
            Requisition::STATUS_CEO_APPROVED,
            Requisition::STATUS_LPO_ISSUED
        ])
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('COUNT(CASE WHEN status = "operations_approved" THEN 1 END) as pending_procurement')
        ->selectRaw('COUNT(CASE WHEN status = "procurement" THEN 1 END) as in_procurement')
        ->selectRaw('COUNT(CASE WHEN status = "ceo_approved" THEN 1 END) as ceo_approved')
        ->selectRaw('COUNT(CASE WHEN status = "lpo_issued" THEN 1 END) as lpo_issued')
        ->first();

    // Get LPO statistics
    $lpoStats = DB::table('lpos')
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('COUNT(CASE WHEN status = "draft" THEN 1 END) as draft')
        ->selectRaw('COUNT(CASE WHEN status = "issued" THEN 1 END) as issued')
        ->selectRaw('COUNT(CASE WHEN status = "delivered" THEN 1 END) as delivered')
        ->first();

    // Get recent LPOs
    $recentLpos = Lpo::with(['requisition', 'supplier'])
        ->latest()
        ->take(5)
        ->get();

    // Share pending count with layout
    view()->share('pendingCount', $requisitionStats->pending_procurement);

    return view('procurement.dashboard', compact(
        'pendingRequisitions',
        'procurementRequisitions',
        'requisitionStats',
        'lpoStats',
        'recentLpos'
    ));
}
}