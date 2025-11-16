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
        // Get all project managers and store managers
        $projectManagers = User::where('role', 'project_manager')->get();
        $storeManagers = User::where('role', 'stores')->get();
        
        return view('admin.projects.create', compact('projectManagers', 'storeManagers'));
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

            //  Attach BOTH managers to the project
             $project->users()->attach([
                $request->project_manager_id => ['role_on_project' => 'project_manager'],
                $request->store_manager_id => ['role_on_project' => 'store_manager']
            ]);

            // Create project store
            $store = Store::create([
                'name' => $project->name . ' Store',
                'code' => 'STORE-' . $project->code,
                'type' => 'project',
                'address' => $project->location,
                'project_id' => $project->id,
                
            ]);

            DB::commit();

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project created successfully with associated store');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create project: ' . $e->getMessage())
                        ->withInput();
        }
    }

   public function show(Project $project)
{
    // Fix: Remove 'stores.manager' relationship - it doesn't exist
    $project->load(['users', 'requisitions', 'stores']);
    
    // Get project manager and store manager manually
    $projectManager = $project->users()->where('role', 'project_manager')->first();
    $storeManager = $project->users()->where('role', 'stores')->first();
    
    return view('admin.projects.show', compact('project', 'projectManager', 'storeManager'));
}

    public function edit(Project $project)
    {
        $projectManagers = User::where('role', 'project_manager')->get();
        $storeManagers = User::where('role', 'stores')->get();
        $currentManager = $project->users()->where('role', 'project_manager')->first();
        $currentStore = $project->stores()->first();
        
        return view('admin.projects.edit', compact('project', 'projectManagers', 'storeManagers', 'currentManager', 'currentStore'));
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

        // Sync BOTH managers (replace the old sync)
        $project->users()->sync([
            $request->project_manager_id => ['role_on_project' => 'project_manager'],
            $request->store_manager_id => ['role_on_project' => 'store_manager']
        ]);

        DB::commit();

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project updated successfully');

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

            // Remove project manager association
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