<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Lpo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CEODashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get requisitions pending CEO approval
        $pendingRequisitions = Requisition::where('status', Requisition::STATUS_CEO_APPROVED)
            ->with(['project', 'requester', 'items', 'lpo', 'lpo.supplier'])
            ->latest()
            ->take(5)
            ->get();

        // Get LPOs pending CEO approval (draft LPOs for CEO approved requisitions)
        $pendingLpos = Lpo::where('status', 'draft')
            ->whereHas('requisition', function($query) {
                $query->where('status', Requisition::STATUS_CEO_APPROVED);
            })
            ->with(['requisition', 'supplier', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // Get statistics
        $stats = DB::table('requisitions')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = "ceo_approved" THEN 1 END) as pending_approval')
            ->selectRaw('COUNT(CASE WHEN status = "lpo_issued" THEN 1 END) as approved')
            ->selectRaw('SUM(estimated_total) as total_amount')
            ->first();

        // Get recent approvals by CEO
        $recentApprovals = Requisition::whereHas('approvals', function($query) use ($user) {
            $query->where('approved_by', $user->id)
                  ->where('role', 'ceo');
        })
        ->with(['project', 'requester'])
        ->latest()
        ->take(5)
        ->get();

        // Share pending count with layout
        view()->share('pendingCount', $pendingRequisitions->count() + $pendingLpos->count());

        return view('ceo.dashboard', compact(
            'pendingRequisitions',
            'pendingLpos',
            'stats',
            'recentApprovals'
        ));
    }
}