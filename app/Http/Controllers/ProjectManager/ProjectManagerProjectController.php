<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectManagerProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get projects managed by this project manager with requisition counts
        $projects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->withCount(['requisitions' => function($query) use ($user) {
            $query->where('requested_by', $user->id);
        }])
        ->latest()
        ->paginate(12);

        return view('project_manager.projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        // Authorization - ensure project manager manages this project
        $this->authorizeProjectAccess($project);

        // Load project with related data
        $project->load(['requisitions' => function($query) {
            $query->with(['requester', 'items'])->latest()->take(10);
        }]);

        // Get requisition statistics for this project
        $requisitionStats = [
            'total' => $project->requisitions()->count(),
            'pending' => $project->requisitions()->where('status', 'pending')->count(),
            'approved' => $project->requisitions()->where('status', 'project_manager_approved')->count(),
            'completed' => $project->requisitions()->whereIn('status', ['completed', 'delivered'])->count(),
        ];

        return view('project_manager.projects.show', compact('project', 'requisitionStats'));
    }

    private function authorizeProjectAccess(Project $project)
    {
        $user = Auth::user();
        
        if (!$project->users->contains($user->id)) {
            abort(403, 'Unauthorized access to this project.');
        }
    }
}