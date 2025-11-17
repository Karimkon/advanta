<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
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
        // Get all project managers, store managers, and engineers
        $projectManagers = User::where('role', 'project_manager')->get();
        $storeManagers = User::where('role', 'stores')->get();
        $engineers = User::where('role', 'engineer')->get(); // Add this line
        
        return view('admin.projects.create', compact('projectManagers', 'storeManagers', 'engineers'));
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

            // Create project store
            $store = Store::create([
                'name' => $project->name . ' Store',
                'code' => 'STORE-' . $project->code,
                'type' => 'project',
                'address' => $project->location,
                'project_id' => $project->id,
            ]);

            DB::commit();

            $message = 'Project created successfully with associated store';
            if ($request->has('engineer_ids')) {
                $message .= ' and ' . count($request->engineer_ids) . ' engineer(s) assigned';
            }

            return redirect()->route('admin.projects.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create project: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function show(Project $project)
    {
        $project->load(['users', 'requisitions', 'stores']);
        
        // Get project manager, store manager, and engineers
        $projectManager = $project->users()->where('role', 'project_manager')->first();
        $storeManager = $project->users()->where('role', 'stores')->first();
        $engineers = $project->users()->where('role', 'engineer')->get();
        
        return view('admin.projects.show', compact('project', 'projectManager', 'storeManager', 'engineers'));
    }

     public function edit(Project $project)
    {
        $projectManagers = User::where('role', 'project_manager')->get();
        $storeManagers = User::where('role', 'stores')->get();
        $engineers = User::where('role', 'engineer')->get();
        
        // Fix the ambiguous column issue by specifying table names
        $currentManager = $project->users()->where('users.role', 'project_manager')->first();
        $currentStoreManager = $project->users()->where('users.role', 'stores')->first();
        
        // Fix: Specify the table for the pluck method
        $currentEngineers = $project->users()
            ->where('users.role', 'engineer')
            ->pluck('users.id')
            ->toArray();
            
        $currentStore = $project->stores()->first();
        
        return view('admin.projects.edit', compact(
            'project', 
            'projectManagers', 
            'storeManagers', 
            'engineers',
            'currentManager', 
            'currentStoreManager',
            'currentEngineers',
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

        // Sync managers and engineers
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

        $project->users()->sync($usersToSync);

        // ✅ FIX: Create store if it doesn't exist
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