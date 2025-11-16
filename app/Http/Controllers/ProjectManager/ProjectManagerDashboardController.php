<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectManagerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get projects managed by this project manager
        $projects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Get requisitions statistics
        $requisitionStats = [
            'total' => Requisition::whereHas('project.users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            
            'pending' => Requisition::whereHas('project.users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'pending')->count(),
            
            'approved' => Requisition::whereHas('project.users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'project_manager_approved')->count(),
            
            'operations_approved' => Requisition::whereHas('project.users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'operations_approved')->count(),
        ];

        // Recent requisitions from projects managed by this PM
        $recentRequisitions = Requisition::whereHas('project.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['project', 'requester', 'items'])
        ->latest()
        ->take(5)
        ->get();

        // Pending approvals - requisitions that need PM approval
        $pendingApprovals = Requisition::whereHas('project.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', 'pending')
        ->with(['project', 'requester', 'items'])
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