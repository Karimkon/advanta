<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['users' => function($query) {
            $query->where('role', 'project_manager');
        }])->latest()->get();

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        // Get all project managers, store managers, engineers, and surveyors
        $projectManagers = User::where('role', 'project_manager')->get();
        $storeManagers = User::where('role', 'stores')->get();
        $engineers = User::where('role', 'engineer')->get();
        $surveyors = User::where('role', 'surveyor')->get(); // New
        
        return view('admin.projects.create', compact('projectManagers', 'storeManagers', 'engineers', 'surveyors'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'project_manager_id' => 'required|exists:users,id',
            'store_manager_id' => 'required|exists:users,id',
            'engineer_ids' => 'nullable|array',
            'engineer_ids.*' => 'exists:users,id',
            'surveyor_ids' => 'nullable|array', // New
            'surveyor_ids.*' => 'exists:users,id', // New
        ]);

        DB::beginTransaction();
        try {
            // Create project
            $project = Project::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget' => $request->budget,
                'status' => $request->status,
            ]);

            // Attach BOTH managers to the project
            $project->users()->attach([
                $request->project_manager_id => ['role_on_project' => 'project_manager'],
                $request->store_manager_id => ['role_on_project' => 'store_manager']
            ]);

            // Add engineers if selected
            if ($request->has('engineer_ids')) {
                foreach ($request->engineer_ids as $engineerId) {
                    $project->users()->attach($engineerId, ['role_on_project' => 'engineer']);
                }
            }

            // Add surveyors if selected - NEW
            if ($request->has('surveyor_ids')) {
                foreach ($request->surveyor_ids as $surveyorId) {
                    $project->users()->attach($surveyorId, ['role_on_project' => 'surveyor']);
                }
            }

            // Create project store
            $store = Store::create([
                'name' => $project->name . ' Store',
                'code' => 'STORE-' . $project->code,
                'type' => 'project',
                'address' => $project->location,
                'project_id' => $project->id,
            ]);

            // Create default milestones for the project - NEW
            $this->createDefaultMilestones($project);

            DB::commit();

            $message = 'Project created successfully with associated store and default milestones';
            if ($request->has('engineer_ids')) {
                $message .= ' and ' . count($request->engineer_ids) . ' engineer(s) assigned';
            }
            if ($request->has('surveyor_ids')) {
                $message .= ' and ' . count($request->surveyor_ids) . ' surveyor(s) assigned';
            }

            return redirect()->route('admin.projects.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create project: ' . $e->getMessage())
                        ->withInput();
        }
    }

     /**
     * Create default construction milestones for a project
     */
private function createDefaultMilestones(Project $project)
{
    $milestones = [
        [
            'title' => 'Foundation (Omusingi)',
            'description' => 'Ground excavation, footings, concrete base, and reinforcement setting',
            'due_date' => $project->start_date->copy()->addDays(30),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.15, // 15% of budget
            'completion_percentage' => 0
        ],
        [
            'title' => 'Substructure',
            'description' => 'Work below ground floor level - retaining walls, basement, ground beams',
            'due_date' => $project->start_date->copy()->addDays(60),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.20, // 20% of budget
            'completion_percentage' => 0
        ],
        [
            'title' => 'Superstructure',
            'description' => 'Above ground level - columns, beams, floors, walls, staircases',
            'due_date' => $project->start_date->copy()->addDays(120),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.25, // 25% of budget
            'completion_percentage' => 0
        ],
        [
            'title' => 'Roofing Level',
            'description' => 'Trusses/slab roof and covering installation',
            'due_date' => $project->start_date->copy()->addDays(150),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.15, // 15% of budget
            'completion_percentage' => 0
        ],
        [
            'title' => 'Finishing Stages',
            'description' => 'Plastering, flooring, windows, doors, electrical, plumbing, painting',
            'due_date' => $project->start_date->copy()->addDays(210),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.20, // 20% of budget
            'completion_percentage' => 0
        ],
        [
            'title' => 'Finalization',
            'description' => 'External works, paving, drainage, landscaping, final inspection',
            'due_date' => $project->end_date ?? $project->start_date->copy()->addDays(240),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.05, // 5% of budget
            'completion_percentage' => 0
        ],
    ];

    foreach ($milestones as $milestoneData) {
        ProjectMilestone::create(array_merge($milestoneData, [
            'project_id' => $project->id,
            'completion_percentage' => 0
        ]));
    }
}

     public function show(Project $project)
    {
        $project->load(['users', 'requisitions', 'stores', 'milestones']);
        
        // Get project team members
        $projectManager = $project->users()->where('role', 'project_manager')->first();
        $storeManager = $project->users()->where('role', 'stores')->first();
        $engineers = $project->users()->where('role', 'engineer')->get();
        $surveyors = $project->users()->where('role', 'surveyor')->get(); // New
        
        return view('admin.projects.show', compact('project', 'projectManager', 'storeManager', 'engineers', 'surveyors'));
    }

     public function edit(Project $project)
    {
        $projectManagers = User::where('role', 'project_manager')->get();
        $storeManagers = User::where('role', 'stores')->get();
        $engineers = User::where('role', 'engineer')->get();
        $surveyors = User::where('role', 'surveyor')->get(); // New
        
        // Get current team members
        $currentManager = $project->users()->where('users.role', 'project_manager')->first();
        $currentStoreManager = $project->users()->where('users.role', 'stores')->first();
        
        $currentEngineers = $project->users()
            ->where('users.role', 'engineer')
            ->pluck('users.id')
            ->toArray();
            
        $currentSurveyors = $project->users() // New
            ->where('users.role', 'surveyor')
            ->pluck('users.id')
            ->toArray();
            
        $currentStore = $project->stores()->first();
        
        return view('admin.projects.edit', compact(
            'project', 
            'projectManagers', 
            'storeManagers', 
            'engineers',
            'surveyors',
            'currentManager', 
            'currentStoreManager',
            'currentEngineers',
            'currentSurveyors',
            'currentStore'
        ));
    }

public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'required|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'project_manager_id' => 'required|exists:users,id',
            'store_manager_id' => 'required|exists:users,id',
            'engineer_ids' => 'nullable|array',
            'engineer_ids.*' => 'exists:users,id',
            'surveyor_ids' => 'nullable|array', // New
            'surveyor_ids.*' => 'exists:users,id', // New
        ]);

        DB::beginTransaction();
        try {
            // Update project
            $project->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget' => $request->budget,
                'status' => $request->status,
            ]);

            // Sync managers, engineers, and surveyors
            $usersToSync = [
                $request->project_manager_id => ['role_on_project' => 'project_manager'],
                $request->store_manager_id => ['role_on_project' => 'store_manager']
            ];

            // Add engineers if selected
            if ($request->has('engineer_ids')) {
                foreach ($request->engineer_ids as $engineerId) {
                    $usersToSync[$engineerId] = ['role_on_project' => 'engineer'];
                }
            }

            // Add surveyors if selected - NEW
            if ($request->has('surveyor_ids')) {
                foreach ($request->surveyor_ids as $surveyorId) {
                    $usersToSync[$surveyorId] = ['role_on_project' => 'surveyor'];
                }
            }

            $project->users()->sync($usersToSync);

            // Create store if it doesn't exist
            if (!$project->stores()->exists()) {
                Store::create([
                    'name' => $project->name . ' Store',
                    'code' => 'STORE-' . $project->code,
                    'type' => 'project',
                    'address' => $project->location,
                    'project_id' => $project->id,
                ]);
            }

            DB::commit();

            $message = 'Project updated successfully';
            if ($request->has('engineer_ids')) {
                $message .= ' with ' . count($request->engineer_ids) . ' engineer(s) assigned';
            }
            if ($request->has('surveyor_ids')) {
                $message .= ' and ' . count($request->surveyor_ids) . ' surveyor(s) assigned';
            }

            return redirect()->route('admin.projects.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update project: ' . $e->getMessage());
        }
    }

    public function destroy(Project $project)
    {
        DB::beginTransaction();
        try {
            // Check if project has requisitions
            if ($project->requisitions()->exists()) {
                return redirect()->route('admin.projects.index')
                    ->with('error', 'Cannot delete project with existing requisitions.');
            }

            // Remove all user associations
            $project->users()->detach();
            
            // Delete project stores
            $project->stores()->delete();
            
            // Delete project
            $project->delete();

            DB::commit();

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete project: ' . $e->getMessage());
        }
    }
}