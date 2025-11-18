<?php

namespace App\Http\Controllers\Surveyor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SurveyorMilestoneController extends Controller
{
    public function index(Project $project)
    {
        // Check if surveyor is assigned to this project
        if (!$project->users()->where('user_id', auth()->id())->where('role_on_project', 'surveyor')->exists()) {
            abort(403, 'You are not assigned to this project as a surveyor.');
        }

        $milestones = $project->milestones()->orderBy('due_date')->get();
        
        return view('surveyor.milestones.index', compact('project', 'milestones'));
    }

    public function show(Project $project, ProjectMilestone $milestone)
    {
        // Check if surveyor is assigned to this project
        if (!$project->users()->where('user_id', auth()->id())->where('role_on_project', 'surveyor')->exists()) {
            abort(403, 'You are not assigned to this project as a surveyor.');
        }

        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        return view('surveyor.milestones.show', compact('project', 'milestone'));
    }

    public function edit(Project $project, ProjectMilestone $milestone)
    {
        // Check if surveyor is assigned to this project
        if (!$project->users()->where('user_id', auth()->id())->where('role_on_project', 'surveyor')->exists()) {
            abort(403, 'You are not assigned to this project as a surveyor.');
        }

        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        return view('surveyor.milestones.edit', compact('project', 'milestone'));
    }

    public function update(Request $request, Project $project, ProjectMilestone $milestone)
    {
        // Check if surveyor is assigned to this project
        if (!$project->users()->where('user_id', auth()->id())->where('role_on_project', 'surveyor')->exists()) {
            abort(403, 'You are not assigned to this project as a surveyor.');
        }

        // Verify milestone belongs to project
        if ($milestone->project_id !== $project->id) {
            abort(404);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,delayed',
            'progress_notes' => 'nullable|string|max:1000',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'actual_cost' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'photo_caption' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'status' => $request->status,
                'progress_notes' => $request->progress_notes,
                'completion_percentage' => $request->completion_percentage,
                'actual_cost' => $request->actual_cost,
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

            return redirect()->route('surveyor.milestones.show', ['project' => $project, 'milestone' => $milestone])
                ->with('success', 'Milestone updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update milestone: ' . $e->getMessage());
        }
    }

    // NEW: Remove photo
    public function removePhoto(Project $project, ProjectMilestone $milestone)
    {
        // Check if surveyor is assigned to this project
        if (!$project->users()->where('user_id', auth()->id())->where('role_on_project', 'surveyor')->exists()) {
            abort(403, 'You are not assigned to this project as a surveyor.');
        }

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