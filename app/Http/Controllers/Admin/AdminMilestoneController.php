<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminMilestoneController extends Controller
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

        return view('admin.milestones.index', compact('projects', 'stats'));
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

        return view('admin.milestones.project', compact('project', 'milestones', 'projectStats'));
    }

    public function show(Project $project, ProjectMilestone $milestone)
    {
        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $milestone->load(['project']);

        return view('admin.milestones.show', compact('project', 'milestone'));
    }

    public function edit(Project $project, ProjectMilestone $milestone)
    {
        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        return view('admin.milestones.edit', compact('project', 'milestone'));
    }

    public function update(Request $request, Project $project, ProjectMilestone $milestone)
    {
        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,delayed',
            'cost_estimate' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'progress_notes' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_caption' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'cost_estimate' => $request->cost_estimate,
                'actual_cost' => $request->actual_cost,
                'completion_percentage' => $request->completion_percentage,
                'progress_notes' => $request->progress_notes,
                'photo_caption' => $request->photo_caption,
            ];

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($milestone->photo_path && Storage::exists($milestone->photo_path)) {
                    Storage::delete($milestone->photo_path);
                }

                // Store new photo
                $photoPath = $request->file('photo')->store('milestone-photos', 'public');
                $updateData['photo_path'] = $photoPath;
            }

            // If marked as completed, set completed_at date
            if ($request->status === 'completed' && $milestone->status !== 'completed') {
                $updateData['completed_at'] = now();
            }

            // If no longer completed, clear completed_at
            if ($request->status !== 'completed' && $milestone->status === 'completed') {
                $updateData['completed_at'] = null;
            }

            $milestone->update($updateData);

            DB::commit();

            return redirect()->route('admin.milestones.show', ['project' => $project, 'milestone' => $milestone])
                ->with('success', 'Milestone updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update milestone: ' . $e->getMessage());
        }
    }

    public function create(Project $project)
    {
        return view('admin.milestones.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,delayed',
            'cost_estimate' => 'nullable|numeric|min:0',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_caption' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $milestoneData = [
                'project_id' => $project->id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'cost_estimate' => $request->cost_estimate,
                'completion_percentage' => $request->completion_percentage,
                'photo_caption' => $request->photo_caption,
            ];

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('milestone-photos', 'public');
                $milestoneData['photo_path'] = $photoPath;
            }

            // If marked as completed, set completed_at date
            if ($request->status === 'completed') {
                $milestoneData['completed_at'] = now();
            }

            ProjectMilestone::create($milestoneData);

            DB::commit();

            return redirect()->route('admin.milestones.project', $project)
                ->with('success', 'Milestone created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create milestone: ' . $e->getMessage());
        }
    }

    public function destroy(Project $project, ProjectMilestone $milestone)
    {
        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // Delete photo if exists
            if ($milestone->photo_path && Storage::exists($milestone->photo_path)) {
                Storage::delete($milestone->photo_path);
            }

            $milestone->delete();

            DB::commit();

            return redirect()->route('admin.milestones.project', $project)
                ->with('success', 'Milestone deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete milestone: ' . $e->getMessage());
        }
    }

    public function removePhoto(Project $project, ProjectMilestone $milestone)
    {
        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            // Delete photo file
            if ($milestone->photo_path && Storage::exists($milestone->photo_path)) {
                Storage::delete($milestone->photo_path);
            }

            // Update milestone record
            $milestone->update([
                'photo_path' => null,
                'photo_caption' => null,
            ]);

            DB::commit();

            return back()->with('success', 'Photo removed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to remove photo: ' . $e->getMessage());
        }
    }
}