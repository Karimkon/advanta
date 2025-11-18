<?php

namespace App\Http\Controllers\Surveyor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;

class SurveyorDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get projects assigned to this surveyor
        $projects = $user->projects()
            ->wherePivot('role_on_project', 'surveyor')
            ->with(['milestones'])
            ->get();

        // Get milestones that need attention (overdue or due soon)
        $attentionMilestones = ProjectMilestone::whereHas('project', function($query) use ($user) {
                $query->whereHas('users', function($userQuery) use ($user) {
                    $userQuery->where('user_id', $user->id)
                             ->where('role_on_project', 'surveyor');
                });
            })
            ->where('status', '!=', 'completed')
            ->where('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date')
            ->get();

        return view('surveyor.dashboard', compact('projects', 'attentionMilestones'));
    }
}