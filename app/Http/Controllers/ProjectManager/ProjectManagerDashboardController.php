<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectManagerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get projects managed by this project manager
        $projects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Get requisition statistics
        $requisitionStats = DB::table('requisitions')
            ->where('requested_by', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending')
            ->selectRaw('COUNT(CASE WHEN status = "project_manager_approved" THEN 1 END) as approved')
            ->selectRaw('COUNT(CASE WHEN status = "operations_approved" THEN 1 END) as operations_approved')
            ->selectRaw('COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected')
            ->first();

        // Recent requisitions
        $recentRequisitions = Requisition::where('requested_by', $user->id)
            ->with(['project', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // Pending approvals (requisitions waiting for project manager approval)
        $pendingApprovals = Requisition::whereHas('project.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->with(['project', 'requester'])
        ->latest()
        ->take(5)
        ->get();

        return view('project_manager.dashboard', compact(
            'projects',
            'requisitionStats',
            'recentRequisitions',
            'pendingApprovals'
        ));
    }
}