<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

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
    // Convert date formats for Safari compatibility before validation
    $request->merge([
        'start_date' => $this->formatDateForDatabase($request->start_date),
        'end_date' => $request->end_date ? $this->formatDateForDatabase($request->end_date) : null,
    ]);

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
        'surveyor_ids' => 'nullable|array',
        'surveyor_ids.*' => 'exists:users,id',
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
 * Format date for database (handle Safari weird formats)
 */
private function formatDateForDatabase($dateString)
{
    if (!$dateString) {
        return null;
    }

    // If it's already in correct format (YYYY-MM-DD), return as is
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
        return $dateString;
    }

    // Handle Safari's weird date format (like 11/25/0007)
    if (preg_match('#(\d{1,2})/(\d{1,2})/(\d{4})#', $dateString, $matches)) {
        $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $day = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        // If year is weird (like 0007), it's likely Safari misinterpreting 2026 as 0007
        // Let's fix this by using the current century
        if (strlen($year) === 4 && intval($year) < 1000) {
            // Extract the last two digits and assume 21st century
            $lastTwoDigits = substr($year, -2);
            $year = '20' . $lastTwoDigits;
        }
        
        return "{$year}-{$month}-{$day}";
    }

    // Handle other common date formats
    $formats = [
        'Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'm-d-Y', 'm/d/Y',
        'Y-m-d H:i:s', 'Y/m/d H:i:s', 'd-m-Y H:i:s', 'd/m/Y H:i:s'
    ];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateString);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }

    // Try to parse as DateTime as last resort
    try {
        $date = new DateTime($dateString);
        return $date->format('Y-m-d');
    } catch (\Exception $e) {
        // If all else fails, return the original string and let validation handle it
        return $dateString;
    }
}

     /**
     * Create default construction milestones for a project
     */
private function createDefaultMilestones(Project $project)
{
    $milestones = [
        // PRELIMINARIES
        [
            'title' => 'Insurances',
            'description' => 'Project insurance setup and documentation',
            'due_date' => $project->start_date->copy()->addDays(7),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Mobilization',
            'description' => 'Mobilization of tools, equipment, manpower and resources',
            'due_date' => $project->start_date->copy()->addDays(10),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.03,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Site Hoarding',
            'description' => 'Hoarding and securing of construction site',
            'due_date' => $project->start_date->copy()->addDays(14),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.01,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Land Scaping and Clearance',
            'description' => 'Site clearance and land preparation',
            'due_date' => $project->start_date->copy()->addDays(21),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Site Facilities Setup',
            'description' => 'Site Offices, Stores, Dormitories, Latrine Set up',
            'due_date' => $project->start_date->copy()->addDays(25),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.015,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Setting and Layout',
            'description' => 'Site setting out and layout marking',
            'due_date' => $project->start_date->copy()->addDays(30),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.005,
            'completion_percentage' => 0
        ],

        // SUB-STRUCTURE
        [
            'title' => 'Foundation Excavations',
            'description' => 'Excavation works for foundations',
            'due_date' => $project->start_date->copy()->addDays(35),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.04,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Foundation Walls',
            'description' => 'Construction of foundation walls',
            'due_date' => $project->start_date->copy()->addDays(42),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.05,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Footings and Bases',
            'description' => 'Concrete footings and base construction',
            'due_date' => $project->start_date->copy()->addDays(48),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.06,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Slab Casting',
            'description' => 'Concrete slab casting for substructure',
            'due_date' => $project->start_date->copy()->addDays(55),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.03,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Splash Appron',
            'description' => 'Splash apron construction',
            'due_date' => $project->start_date->copy()->addDays(58),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.01,
            'completion_percentage' => 0
        ],
        [
            'title' => 'First Fixtures M&E',
            'description' => 'First mechanical and electrical fixtures installation',
            'due_date' => $project->start_date->copy()->addDays(60),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.01,
            'completion_percentage' => 0
        ],

        // SUPER STRUCTURE
        [
            'title' => 'Walling',
            'description' => 'Construction of walls and partitions',
            'due_date' => $project->start_date->copy()->addDays(70),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.07,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Ring Beams',
            'description' => 'Ring beam construction and installation',
            'due_date' => $project->start_date->copy()->addDays(80),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.04,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Columns',
            'description' => 'Column construction and reinforcement',
            'due_date' => $project->start_date->copy()->addDays(85),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.05,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Plastering',
            'description' => 'Internal and external plastering works',
            'due_date' => $project->start_date->copy()->addDays(100),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.03,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Doors Installation',
            'description' => 'Installation of doors and frames',
            'due_date' => $project->start_date->copy()->addDays(105),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Windows Installation',
            'description' => 'Installation of windows and frames',
            'due_date' => $project->start_date->copy()->addDays(108),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Second Fixtures M&E',
            'description' => 'Second phase mechanical and electrical fixtures',
            'due_date' => $project->start_date->copy()->addDays(112),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.015,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Joinery and Fittings',
            'description' => 'Joinery works and fittings installation',
            'due_date' => $project->start_date->copy()->addDays(115),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.015,
            'completion_percentage' => 0
        ],

        // ROOFING
        [
            'title' => 'Batten and Trusses',
            'description' => 'Roof batten and trusses installation',
            'due_date' => $project->start_date->copy()->addDays(120),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.04,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Facial Boards',
            'description' => 'Facial boards installation',
            'due_date' => $project->start_date->copy()->addDays(125),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Roof Covering',
            'description' => 'Main roof covering installation',
            'due_date' => $project->start_date->copy()->addDays(135),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.06,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Eves Installation',
            'description' => 'Eaves construction and finishing',
            'due_date' => $project->start_date->copy()->addDays(140),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Gutters Installation',
            'description' => 'Rainwater gutters and downpipes installation',
            'due_date' => $project->start_date->copy()->addDays(145),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.01,
            'completion_percentage' => 0
        ],

        // FINISHING
        [
            'title' => 'Floor Finishing',
            'description' => 'Floor finishes and tiling works',
            'due_date' => $project->start_date->copy()->addDays(150),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.05,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Wall Finishing',
            'description' => 'Wall finishes and painting',
            'due_date' => $project->start_date->copy()->addDays(160),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.04,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Final Fittings M&E',
            'description' => 'Final mechanical and electrical fittings',
            'due_date' => $project->start_date->copy()->addDays(170),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.06,
            'completion_percentage' => 0
        ],

        // EXTERNAL WORKS
        [
            'title' => 'Landscaping',
            'description' => 'Landscaping and garden works',
            'due_date' => $project->start_date->copy()->addDays(180),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.03,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Wall Fence',
            'description' => 'Perimeter wall fence construction',
            'due_date' => $project->start_date->copy()->addDays(185),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Paving',
            'description' => 'Paving and hard landscaping',
            'due_date' => $project->start_date->copy()->addDays(190),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Gardening',
            'description' => 'Garden setup and planting',
            'due_date' => $project->start_date->copy()->addDays(195),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.01,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Water Tanks',
            'description' => 'Water tank installation and setup',
            'due_date' => $project->start_date->copy()->addDays(200),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.01,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Generator House',
            'description' => 'Generator house provisions and setup',
            'due_date' => $project->start_date->copy()->addDays(205),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.005,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Site Cleaning',
            'description' => 'Final site cleaning and house keeping',
            'due_date' => $project->start_date->copy()->addDays(210),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.005,
            'completion_percentage' => 0
        ],

        // HAND OVER
        [
            'title' => 'Commissioning',
            'description' => 'Systems commissioning and testing',
            'due_date' => $project->start_date->copy()->addDays(215),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.02,
            'completion_percentage' => 0
        ],
        [
            'title' => 'Handover',
            'description' => 'Final project handover to client',
            'due_date' => $project->end_date ?? $project->start_date->copy()->addDays(220),
            'status' => 'pending',
            'cost_estimate' => $project->budget * 0.03,
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