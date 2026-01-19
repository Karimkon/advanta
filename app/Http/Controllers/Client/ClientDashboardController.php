<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientDashboardController extends Controller
{
    /**
     * Show client dashboard with project progress
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        $projects = $client->projects()->with(['milestones'])->get();

        // Calculate statistics for the view
        $totalProjects = $projects->count();
        $activeProjects = $projects->where('status', 'active')->count() + $projects->where('status', 'in_progress')->count();
        $totalMilestones = $projects->sum(fn($p) => $p->milestones->count());

        // Calculate average progress across all projects
        $averageProgress = 0;
        if ($totalProjects > 0) {
            $totalProgress = $projects->sum(function ($project) {
                $milestoneCount = $project->milestones->count();
                if ($milestoneCount === 0) return 0;
                $completedCount = $project->milestones->where('status', 'completed')->count();
                return ($completedCount / $milestoneCount) * 100;
            });
            $averageProgress = $totalProgress / $totalProjects;
        }

        return view('client.dashboard', compact('client', 'projects', 'totalProjects', 'activeProjects', 'totalMilestones', 'averageProgress'));
    }

    /**
     * Show project details with milestones
     */
    public function projectMilestones($id)
    {
        $client = Auth::guard('client')->user();

        // Ensure client has access to this project
        $project = $client->projects()
            ->with(['milestones' => function ($query) {
                $query->orderBy('due_date', 'asc');
            }])
            ->findOrFail($id);

        // Calculate progress
        $totalMilestones = $project->milestones->count();
        $completedMilestones = $project->milestones->where('status', 'completed')->count();
        $progress = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;

        // Group milestones by status
        $milestonesByStatus = [
            'completed' => $project->milestones->where('status', 'completed'),
            'in_progress' => $project->milestones->where('status', 'in_progress'),
            'pending' => $project->milestones->where('status', 'pending'),
        ];

        return view('client.project', compact('project', 'progress', 'totalMilestones', 'completedMilestones', 'milestonesByStatus'));
    }

    /**
     * Show milestone details with photos
     */
    public function milestoneDetail($projectId, $milestoneId)
    {
        $client = Auth::guard('client')->user();

        // Ensure client has access to this project
        $project = $client->projects()->findOrFail($projectId);
        $milestone = $project->milestones()->findOrFail($milestoneId);

        return view('client.milestone', compact('project', 'milestone'));
    }
}
