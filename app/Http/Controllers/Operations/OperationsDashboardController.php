<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationsDashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    
    // Get requisitions pending operations approval
    $pendingRequisitions = Requisition::where('status', Requisition::STATUS_PROJECT_MANAGER_APPROVED)
        ->with(['project', 'requester', 'items'])
        ->latest()
        ->take(5)
        ->get();

    // Get requisition statistics for operations
    $requisitionStats = DB::table('requisitions')
        ->whereIn('status', [
            Requisition::STATUS_PROJECT_MANAGER_APPROVED,
            Requisition::STATUS_OPERATIONS_APPROVED,
            Requisition::STATUS_PROCUREMENT
        ])
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('COUNT(CASE WHEN status = "project_manager_approved" THEN 1 END) as pending_operations')
        ->selectRaw('COUNT(CASE WHEN status = "operations_approved" THEN 1 END) as approved')
        ->selectRaw('COUNT(CASE WHEN status = "procurement" THEN 1 END) as sent_to_procurement')
        ->first();

    // Get recent requisitions processed by operations
    $recentProcessed = Requisition::whereHas('approvals', function($query) use ($user) {
        $query->where('approved_by', $user->id)
              ->where('role', 'operations');
    })
    ->with(['project', 'requester'])
    ->latest()
    ->take(5)
    ->get();

    // Share pending count with layout
    view()->share('pendingCount', $requisitionStats->pending_operations);

    return view('operations.dashboard', compact(
        'pendingRequisitions',
        'requisitionStats',
        'recentProcessed'
    ));
}
}