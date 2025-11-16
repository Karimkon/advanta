<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngineerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get projects assigned to this engineer
        $projects = $user->projects()->count();
        
        // Get requisitions created by this engineer
        $requisitions = Requisition::where('requested_by', $user->id)->count();
        $pendingRequisitions = Requisition::where('requested_by', $user->id)
            ->where('status', 'pending')
            ->count();
        $approvedRequisitions = Requisition::where('requested_by', $user->id)
            ->where('status', 'project_manager_approved')
            ->count();
        
        // Recent requisitions
        $recentRequisitions = Requisition::where('requested_by', $user->id)
            ->with(['project', 'items'])
            ->latest()
            ->take(5)
            ->get();

        return view('engineer.dashboard', compact(
            'projects',
            'requisitions',
            'pendingRequisitions',
            'approvedRequisitions',
            'recentRequisitions'
        ));
    }
}