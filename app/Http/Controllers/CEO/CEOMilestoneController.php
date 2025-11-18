<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;

class CEOMilestoneController extends Controller
{
    public function index()
    {
        $projects = Project::with(['milestones' => function($query) {
            $query->orderBy('due_date');
        }])->whereHas('milestones')->get();

        $stats = [
            'total_projects' => $projects->count(),
            'total_milestones' => $projects->sum(function($project) {
                return $project->milestones->count();
            }),
            'completed_milestones' => $projects->sum(function($project) {
                return $project->milestones->where('status', 'completed')->count();
            }),
            'overdue_milestones' => $projects->sum(function($project) {
                return $project->milestones->where('due_date', '<', now())
                    ->where('status', '!=', 'completed')
                    ->count();
            }),
        ];

        return view('ceo.milestones.index', compact('projects', 'stats'));
    }

    public function projectMilestones(Project $project)
    {
        $milestones = $project->milestones()
            ->with(['project'])
            ->orderBy('due_date')
            ->get();

        $projectStats = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('status', 'completed')->count(),
            'in_progress' => $milestones->where('status', 'in_progress')->count(),
            'pending' => $milestones->where('status', 'pending')->count(),
            'overdue' => $milestones->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
        ];

        return view('ceo.milestones.project', compact('project', 'milestones', 'projectStats'));
    }

    public function show(Project $project, ProjectMilestone $milestone)
    {
        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $milestone->load(['project']);

        return view('ceo.milestones.show', compact('project', 'milestone'));
    }
}