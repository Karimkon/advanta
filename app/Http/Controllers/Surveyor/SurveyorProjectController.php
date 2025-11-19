<?php

namespace App\Http\Controllers\Surveyor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class SurveyorProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get projects assigned to this surveyor
        $projects = $user->projects()
            ->wherePivot('role_on_project', 'surveyor')
            ->with(['milestones'])
            ->withCount(['milestones'])
            ->get();

        // Calculate progress for each project
        $projects->each(function ($project) {
            $totalMilestones = $project->milestones->count();
            $completedMilestones = $project->milestones->where('status', 'completed')->count();
            $project->progress = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;
        });

        return view('surveyor.projects.index', compact('projects'));
    }
}