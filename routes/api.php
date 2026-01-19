<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Project;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionApproval;
use App\Models\Lpo;
use App\Models\LpoItem;
use App\Models\LpoReceivedItem;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\StoreRelease;
use App\Models\StoreReleaseItem;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\ProductCatalog;
use App\Models\ProductCategory;
use App\Models\ProjectMilestone;
use App\Models\LaborWorker;
use App\Models\LaborPayment;
use App\Models\Subcontractor;
use App\Models\SubcontractorPayment;
use App\Models\ProjectSubcontractor;
use App\Models\StaffReport;
use App\Models\QhseReport;
use App\Models\InAppNotification;
use App\Models\Equipment;
use App\Models\Client;
use App\Models\DeviceToken;

/*
|--------------------------------------------------------------------------
| API Routes for Advanta Flutter Mobile App
|--------------------------------------------------------------------------
| Construction Project Management & Procurement System
| Token-based authentication using Laravel Sanctum
| All responses are JSON format
*/

// ====================
// PUBLIC ROUTES (No Authentication Required)
// ====================

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Advanta API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});

// Authentication - Login
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Create a new token
    $token = $user->createToken('mobile-app-' . $user->role)->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'shop_id' => $user->shop_id,
            'back_debt' => $user->back_debt,
            'created_at' => $user->created_at,
        ],
    ]);
});

// Staff Reports - Public submission with access code
Route::post('/public/staff-reports', function (Request $request) {
    $request->validate([
        'access_code' => 'required|string',
        'staff_name' => 'required|string|max:255',
        'staff_email' => 'nullable|email',
        'report_type' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'report_date' => 'required|date',
        'attachments.*' => 'nullable|file|max:10240', // 10MB max
    ]);

    // Validate access code
    if ($request->access_code !== 'ADVANTA2024') {
        return response()->json([
            'success' => false,
            'message' => 'Invalid access code',
        ], 403);
    }

    $attachments = [];
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('staff-reports', 'public');
            $attachments[] = $path;
        }
    }

    $report = StaffReport::create([
        'staff_name' => $request->staff_name,
        'staff_email' => $request->staff_email,
        'report_type' => $request->report_type,
        'title' => $request->title,
        'description' => $request->description,
        'report_date' => $request->report_date,
        'access_code' => $request->access_code,
        'attachments' => json_encode($attachments),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Report submitted successfully',
        'data' => $report,
    ], 201);
});

// Staff Reports - Get reports by access code (for viewing own reports)
Route::post('/public/staff-reports/my-reports', function (Request $request) {
    $request->validate([
        'access_code' => 'required|string',
        'staff_email' => 'required|email',
    ]);

    if ($request->access_code !== 'ADVANTA2024') {
        return response()->json([
            'success' => false,
            'message' => 'Invalid access code',
        ], 403);
    }

    $reports = StaffReport::where('staff_email', $request->staff_email)
        ->orderBy('report_date', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $reports,
    ]);
});

// QHSE Reports - Public submission with access code
Route::post('/public/qhse-reports', function (Request $request) {
    $request->validate([
        'access_code' => 'required|string',
        'staff_name' => 'required|string|max:255',
        'staff_email' => 'nullable|email',
        'report_type' => 'required|in:safety,quality,health,environment,incident,companydocuments',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'report_date' => 'required|date',
        'location' => 'required|string|max:255',
        'department' => 'required|string|max:255',
        'attachments.*' => 'nullable|file|max:10240',
    ]);

    // Validate access code
    if ($request->access_code !== 'QHSE2024') {
        return response()->json([
            'success' => false,
            'message' => 'Invalid access code',
        ], 403);
    }

    $attachments = [];
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('qhse-reports', 'public');
            $attachments[] = $path;
        }
    }

    $report = QhseReport::create([
        'staff_name' => $request->staff_name,
        'staff_email' => $request->staff_email,
        'report_type' => $request->report_type,
        'title' => $request->title,
        'description' => $request->description,
        'report_date' => $request->report_date,
        'location' => $request->location,
        'department' => $request->department,
        'access_code' => $request->access_code,
        'attachments' => json_encode($attachments),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'QHSE report submitted successfully',
        'data' => $report,
    ], 201);
});

// QHSE Reports - Get reports by access code (for viewing own or all reports)
Route::post('/public/qhse-reports/my-reports', function (Request $request) {
    $request->validate([
        'access_code' => 'required|string',
        'staff_email' => 'nullable|email',
    ]);

    if ($request->access_code !== 'QHSE2024') {
        return response()->json([
            'success' => false,
            'message' => 'Invalid access code',
        ], 403);
    }

    $query = QhseReport::query();

    // If email is provided, filter by email
    if ($request->staff_email) {
        $query->where('staff_email', $request->staff_email);
    }

    $reports = $query->orderBy('report_date', 'desc')->get();

    return response()->json([
        'success' => true,
        'data' => $reports,
    ]);
});

// QHSE Reports - Get all reports (public read-only access with code)
Route::get('/public/qhse-reports', function (Request $request) {
    if ($request->header('X-Access-Code') !== 'QHSE2024' && $request->access_code !== 'QHSE2024') {
        return response()->json([
            'success' => false,
            'message' => 'Invalid access code',
        ], 403);
    }

    $reports = QhseReport::orderBy('report_date', 'desc')
        ->limit(50)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $reports,
    ]);
});

// ====================
// AUTHENTICATED ROUTES
// ====================
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    });

    // Get current user
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'shop_id' => $user->shop_id,
                'back_debt' => $user->back_debt,
                'created_at' => $user->created_at,
            ],
        ]);
    });

    // Update Profile
    Route::put('/user/profile', function (Request $request) {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'phone']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    });

    // Change Password
    Route::put('/user/change-password', function (Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully']);
    });

    // ==================== USER MANAGEMENT (Admin Only) ====================
    Route::prefix('users')->group(function () {
        
        // List all users
        Route::get('/', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $query = User::query();
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }
            
            $users = $query->orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        });
        
        // Create user
        Route::post('/', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'role' => 'required|string',
                'phone' => 'nullable|string|max:255',
                'shop_id' => 'nullable|integer',
            ]);
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'shop_id' => $request->shop_id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);
        });
        
        // Update user
        Route::put('/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
            
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|min:8',
                'role' => 'sometimes|required|string',
                'phone' => 'nullable|string|max:255',
                'shop_id' => 'nullable|integer',
            ]);
            
            $data = $request->only(['name', 'email', 'role', 'phone', 'shop_id']);
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            
            $user->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ]);
        });
        
        // Delete user
        Route::delete('/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $user = User::find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
            
            // Prevent deleting self
            if ($user->id === $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'You cannot delete yourself'], 400);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        });
    });

    // ==================== DASHBOARD ====================
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        $stats = [];

        // Role-based dashboard stats
        switch ($user->role) {
            case 'admin':
            case 'ceo':
                $stats = [
                    'total_projects' => Project::count(),
                    'active_projects' => Project::where('status', 'active')->count(),
                    'total_users' => User::count(),
                    'pending_requisitions' => Requisition::where('status', 'pending')->count(),
                    'pending_lpos' => Lpo::where('status', 'pending')->count(),
                    'pending_payments' => Payment::where('ceo_approved', false)->where('status', 'pending')->count(),
                    'total_suppliers' => Supplier::count(),
                    'total_inventory_value' => InventoryItem::sum(DB::raw('quantity * unit_price')),
                    'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
                    'total_expenses' => Expense::sum('amount'),
                    'monthly_budget' => 50000000, // Default monthly budget
                    'monthly_spent' => Expense::whereMonth('created_at', now()->month)->sum('amount'),
                ];
                break;

            case 'finance':
                // Get delivered requisitions waiting for payment creation
                $deliveredForPayment = Requisition::where('status', 'delivered')
                    ->whereHas('lpo', function($query) {
                        $query->where('status', 'delivered');
                    })
                    ->count();

                $currentMonthPayments = Payment::whereMonth('paid_on', now()->month)
                    ->whereNotNull('paid_on')
                    ->get();

                $stats = [
                    'pending_payments_for_creation' => $deliveredForPayment,
                    'pending_ceo_approval' => Payment::where('approval_status', 'pending')->count(),
                    'total_payments_this_month' => $currentMonthPayments->count(),
                    'total_amount_this_month' => $currentMonthPayments->sum('amount'),
                    'total_vat_this_month' => $currentMonthPayments->sum('vat_amount'),
                    'total_expenses' => Expense::sum('amount'),
                ];
                break;

            case 'procurement':
                $stats = [
                    'pending_requisitions' => Requisition::where('status', 'procurement')->count(),
                    'total_lpos' => Lpo::count(),
                    'pending_lpos' => Lpo::where('status', 'pending')->count(),
                    'delivered_lpos' => Lpo::where('status', 'delivered')->count(),
                    'total_suppliers' => Supplier::count(),
                ];
                break;

            case 'stores':
            case 'store_manager':
                // Get stores assigned to this user (via project assignments)
                $userProjectIds = DB::table('project_user')
                    ->where('user_id', $user->id)
                    ->pluck('project_id')
                    ->toArray();

                $storeIds = Store::whereIn('project_id', $userProjectIds)->pluck('id')->toArray();

                // If no specific stores assigned, show all (for admin-level stores users)
                if (empty($storeIds)) {
                    $storeIds = Store::pluck('id')->toArray();
                }

                // Count store requisitions pending release (approved by PM or partial release)
                $pendingRequisitionsQuery = Requisition::where('type', 'store')
                    ->whereIn('status', ['project_manager_approved', 'partial_release']);

                // Filter by user's projects if assigned
                if (!empty($userProjectIds)) {
                    $pendingRequisitionsQuery->whereIn('project_id', $userProjectIds);
                }

                $stats = [
                    'total_stores' => count($storeIds),
                    'total_inventory_items' => InventoryItem::whereIn('store_id', $storeIds)->count(),
                    'low_stock_items' => InventoryItem::whereIn('store_id', $storeIds)
                        ->whereColumn('quantity', '<=', 'reorder_level')->count(),
                    'pending_releases' => $pendingRequisitionsQuery->count(),
                    'pending_lpo_deliveries' => Lpo::where('status', 'issued')->count(),
                    'total_inventory_value' => InventoryItem::whereIn('store_id', $storeIds)
                        ->sum(DB::raw('quantity * unit_price')),
                ];
                break;

            case 'project_manager':
            case 'engineer':
                $userProjects = $user->projects->pluck('id');
                $stats = [
                    'my_projects' => $user->projects->count(),
                    'my_requisitions' => Requisition::where('requested_by', $user->id)->count(),
                    'pending_requisitions' => Requisition::where('requested_by', $user->id)
                        ->where('status', 'pending')->count(),
                    'approved_requisitions' => Requisition::where('requested_by', $user->id)
                        ->whereIn('status', ['completed', 'lpo_issued'])->count(),
                ];
                break;

            case 'surveyor':
                $userProjects = $user->projects->pluck('id');
                $stats = [
                    'my_projects' => $user->projects->count(),
                    'total_milestones' => ProjectMilestone::whereIn('project_id', $userProjects)->count(),
                    'completed_milestones' => ProjectMilestone::whereIn('project_id', $userProjects)
                        ->where('status', 'completed')->count(),
                    'delayed_milestones' => ProjectMilestone::whereIn('project_id', $userProjects)
                        ->where('status', 'delayed')->count(),
                ];
                break;

            default:
                $stats = ['message' => 'Dashboard stats not configured for this role'];
        }

        // Get recent notifications
        $notifications = InAppNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'notifications' => $notifications,
            'user_role' => $user->role,
        ]);
    });

    // ==================== PROJECTS ====================
    Route::prefix('projects')->group(function () {

        // Get all projects
        Route::get('/', function (Request $request) {
            $user = $request->user();
            $query = Project::query();

            // Filter by user's assigned projects for non-admin/ceo/finance roles
            // Finance needs access to all projects for labor/subcontractor management
            if (!in_array($user->role, ['admin', 'ceo', 'finance'])) {
                $projectIds = $user->projects->pluck('id');
                $query->whereIn('id', $projectIds);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $projects = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $projects,
            ]);
        });

        // Get single project
        Route::get('/{id}', function (Request $request, $id) {
            $project = Project::with(['milestones', 'stores'])->find($id);

            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            // Check access
            $user = $request->user();
            if (!in_array($user->role, ['admin', 'ceo']) && !$user->projects->contains($id)) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $project,
            ]);
        });

        // Create project (Admin only)
        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'code' => 'nullable|string|max:255|unique:projects,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'budget' => 'nullable|numeric|min:0',
            ]);

            $project = Project::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'data' => $project,
            ], 201);
        });

        // Update project (Admin only)
        Route::put('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $project = Project::find($id);
            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            $request->validate([
                'code' => 'nullable|string|max:255|unique:projects,code,' . $id,
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'budget' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:active,completed,on_hold',
            ]);

            $project->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project,
            ]);
        });

        // Get project milestones
        Route::get('/{id}/milestones', function ($id) {
            $milestones = ProjectMilestone::where('project_id', $id)
                ->orderBy('due_date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $milestones,
            ]);
        });

        // Get project requisitions
        Route::get('/{id}/requisitions', function ($id) {
            $requisitions = Requisition::with(['requester', 'items'])
                ->where('project_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requisitions,
            ]);
        });

        // Get project inventory
        Route::get('/{id}/inventory', function ($id) {
            $stores = Store::where('project_id', $id)
                ->with(['inventoryItems'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $stores,
            ]);
        });
    });

    // ==================== REQUISITIONS ====================
    Route::prefix('requisitions')->group(function () {

        // Get all requisitions
        Route::get('/', function (Request $request) {
            $user = $request->user();
            $query = Requisition::with(['project', 'requester', 'items', 'store']);

            // Role-based filtering
            switch ($user->role) {
                case 'engineer':
                    // Engineers see only their own requisitions
                    $query->where('requested_by', $user->id);
                    break;

                case 'project_manager':
                    // Project managers see requisitions for their projects
                    $userProjectIds = DB::table('project_user')
                        ->where('user_id', $user->id)
                        ->pluck('project_id');

                    if ($userProjectIds->isNotEmpty()) {
                        $query->whereIn('project_id', $userProjectIds);
                    } else {
                        // If not assigned to any projects, show their own requisitions
                        $query->where('requested_by', $user->id);
                    }
                    break;

                case 'operations':
                    // Operations sees purchase requisitions approved by PM and their own approved ones
                    $query->where('type', 'purchase')
                        ->whereIn('status', ['project_manager_approved', 'operations_approved', 'procurement']);
                    break;
                case 'procurement':
                    $query->whereIn('status', ['operations_approved', 'procurement', 'ceo_approved']);
                    break;
                case 'ceo':
                    $query->where('status', 'operations_approved')
                        ->orWhere('status', 'ceo_approved')
                        ->orWhere('status', 'procurement');
                    break;
                case 'stores':
                case 'store_manager':
                    // Get stores assigned to this user (via project assignments)
                    $userProjectIds = DB::table('project_user')
                        ->where('user_id', $user->id)
                        ->pluck('project_id')
                        ->toArray();

                    // Store requisitions that are approved by project manager (ready for release)
                    // or completed/in-progress releases
                    $query->where('type', 'store')
                        ->whereIn('status', ['project_manager_approved', 'releasing', 'completed', 'partial_release']);

                    // Filter by user's assigned projects if they have any
                    if (!empty($userProjectIds)) {
                        $query->whereIn('project_id', $userProjectIds);
                    }
                    break;
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by project
            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            $perPage = $request->get('per_page', 20);
            $requisitions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $requisitions->items(),
                'meta' => [
                    'current_page' => $requisitions->currentPage(),
                    'last_page' => $requisitions->lastPage(),
                    'total' => $requisitions->total(),
                ],
            ]);
        });

        // Get single requisition
        Route::get('/{id}', function ($id) {
            $requisition = Requisition::with(['project', 'requester', 'items', 'approvals.approver', 'store'])
                ->find($id);

            if (!$requisition) {
                return response()->json(['success' => false, 'message' => 'Requisition not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $requisition,
            ]);
        });

        // Update requisition (for operations to edit before approval)
        Route::put('/{id}', function (Request $request, $id) {
            $requisition = Requisition::find($id);

            if (!$requisition) {
                return response()->json(['success' => false, 'message' => 'Requisition not found'], 404);
            }

            // Only operations can update requisitions
            if (!in_array($request->user()->role, ['operations', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'urgency' => 'nullable|in:low,normal,high,urgent',
                'reason' => 'nullable|string',
                'items' => 'nullable|array',
                'items.*.id' => 'required|exists:requisition_items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.has_vat' => 'nullable|boolean',
            ]);

            DB::beginTransaction();
            try {
                // Update requisition fields
                if ($request->has('urgency')) {
                    $requisition->urgency = $request->urgency;
                }
                if ($request->has('reason')) {
                    $requisition->reason = $request->reason;
                }

                // Update items if provided
                if ($request->has('items')) {
                    foreach ($request->items as $itemData) {
                        $item = RequisitionItem::find($itemData['id']);
                        if ($item && $item->requisition_id == $requisition->id) {
                            $item->quantity = $itemData['quantity'];
                            $item->unit_price = $itemData['unit_price'];
                            $item->total_price = $itemData['quantity'] * $itemData['unit_price'];
                            $item->has_vat = $itemData['has_vat'] ?? 0;
                            $item->save();
                        }
                    }

                    // Recalculate estimated total
                    $estimatedTotal = RequisitionItem::where('requisition_id', $requisition->id)
                        ->sum(DB::raw('quantity * unit_price'));
                    $requisition->estimated_total = $estimatedTotal;
                }

                $requisition->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Requisition updated successfully',
                    'data' => $requisition->load(['items']),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update requisition: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Create requisition
        Route::post('/', function (Request $request) {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'type' => 'required|in:store,purchase',
                'urgency' => 'required|in:low,normal,high',
                'reason' => 'nullable|string',
                'store_id' => 'required_if:type,store|exists:stores,id',
                'items' => 'required|array|min:1',
                'items.*.product_catalog_id' => 'nullable|exists:product_catalogs,id',
                'items.*.item_description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit' => 'required|string',
                'items.*.estimated_unit_price' => 'nullable|numeric|min:0',
            ]);

            DB::beginTransaction();
            try {
                // Generate reference number
                $ref = 'REQ-' . strtoupper(uniqid()) . '-' . date('Ymd');

                // Calculate estimated total
                $estimatedTotal = 0;
                foreach ($request->items as $item) {
                    $estimatedTotal += ($item['quantity'] ?? 0) * ($item['estimated_unit_price'] ?? 0);
                }

                $requisition = Requisition::create([
                    'ref' => $ref,
                    'project_id' => $request->project_id,
                    'requested_by' => $request->user()->id,
                    'type' => $request->type,
                    'urgency' => $request->urgency,
                    'reason' => $request->reason,
                    'store_id' => $request->store_id,
                    'status' => 'pending',
                    'estimated_total' => $estimatedTotal,
                ]);

                // Create requisition items
                foreach ($request->items as $item) {
                    $totalPrice = ($item['quantity'] ?? 0) * ($item['estimated_unit_price'] ?? 0);
                    RequisitionItem::create([
                        'requisition_id' => $requisition->id,
                        'product_catalog_id' => $item['product_catalog_id'] ?? null,
                        'name' => $item['item_description'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['estimated_unit_price'] ?? 0,
                        'total_price' => $totalPrice,
                        'from_store' => $request->type === 'store',
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Requisition created successfully',
                    'data' => $requisition->load(['items']),
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create requisition: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Approve/Reject requisition
        Route::post('/{id}/approve', function (Request $request, $id) {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'comments' => 'nullable|string',
            ]);

            $requisition = Requisition::find($id);
            if (!$requisition) {
                return response()->json(['success' => false, 'message' => 'Requisition not found'], 404);
            }

            $user = $request->user();
            $currentStatus = $requisition->status;
            $newStatus = null;

            // Determine new status based on role and current status
            if ($request->action === 'approve') {
                switch ($user->role) {
                    case 'project_manager':
                        if ($currentStatus === 'pending') {
                            $newStatus = 'project_manager_approved';
                        }
                        break;
                    case 'operations':
                        if ($currentStatus === 'project_manager_approved' && $requisition->type === 'purchase') {
                            $newStatus = 'operations_approved';
                        }
                        break;
                    case 'ceo':
                        if ($currentStatus === 'operations_approved' || $currentStatus === 'procurement') {
                            $newStatus = 'ceo_approved';
                        }
                        break;
                }
            } else {
                $newStatus = 'rejected';
            }

            if (!$newStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot perform this action on this requisition',
                ], 403);
            }

            DB::beginTransaction();
            try {
                // Update requisition status
                $requisition->update(['status' => $newStatus]);

                // Record approval
                RequisitionApproval::create([
                    'requisition_id' => $requisition->id,
                    'approved_by' => $user->id,
                    'action' => $request->action,
                    'comments' => $request->comments,
                    'role' => $user->role,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => ucfirst($request->action) . 'd successfully',
                    'data' => $requisition->fresh(['approvals']),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process approval: ' . $e->getMessage(),
                ], 500);
            }
        });
    });

    // ==================== LPOs (Local Purchase Orders) ====================
    Route::prefix('lpos')->group(function () {

        // Get all LPOs
        Route::get('/', function (Request $request) {
            $query = Lpo::with(['supplier', 'requisition.project', 'requisition.requester', 'items']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            $perPage = $request->get('per_page', 20);
            $lpos = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $lpos->items(),
                'meta' => [
                    'current_page' => $lpos->currentPage(),
                    'last_page' => $lpos->lastPage(),
                    'total' => $lpos->total(),
                ],
            ]);
        });

        // Get single LPO
        Route::get('/{id}', function ($id) {
            $lpo = Lpo::with(['supplier', 'requisition.project', 'requisition.requester', 'items', 'receivedItems'])->find($id);

            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $lpo,
            ]);
        });

        // Update LPO (CEO can edit items before approval)
        Route::put('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['ceo', 'admin', 'procurement'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $lpo = Lpo::find($id);
            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            // Only draft or pending LPOs can be edited
            if (!in_array($lpo->status, ['draft', 'pending'])) {
                return response()->json(['success' => false, 'message' => 'Only draft or pending LPOs can be edited'], 400);
            }

            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:lpo_items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.has_vat' => 'nullable|boolean',
                'delivery_date' => 'nullable|date',
                'terms' => 'nullable|string',
            ]);

            DB::beginTransaction();
            try {
                // Update LPO items
                $subtotal = 0;
                $vatAmount = 0;

                foreach ($request->items as $itemData) {
                    $item = LpoItem::where('id', $itemData['id'])
                        ->where('lpo_id', $lpo->id)
                        ->first();

                    if ($item) {
                        $hasVat = $itemData['has_vat'] ?? $item->has_vat;
                        $vatRate = $itemData['vat_rate'] ?? $item->vat_rate ?? 18.0;
                        $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                        $itemVat = $hasVat ? ($itemTotal * $vatRate / 100) : 0;

                        $item->update([
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $itemTotal + $itemVat,
                            'has_vat' => $hasVat,
                        ]);

                        $subtotal += $itemTotal;
                        $vatAmount += $itemVat;
                    }
                }

                // Update LPO totals
                $lpo->update([
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'total' => $subtotal + $vatAmount,
                    'delivery_date' => $request->delivery_date ?? $lpo->delivery_date,
                    'terms' => $request->terms ?? $lpo->terms,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'LPO updated successfully',
                    'data' => $lpo->fresh(['items', 'supplier']),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update LPO: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Delete LPO (CEO can delete draft/pending LPOs)
        Route::delete('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['ceo', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $lpo = Lpo::find($id);
            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            // Only draft or pending LPOs can be deleted
            if (!in_array($lpo->status, ['draft', 'pending'])) {
                return response()->json(['success' => false, 'message' => 'Only draft or pending LPOs can be deleted'], 400);
            }

            DB::beginTransaction();
            try {
                // Delete LPO items first
                LpoItem::where('lpo_id', $lpo->id)->delete();

                // Update requisition status back to procurement if needed
                if ($lpo->requisition) {
                    $lpo->requisition->update(['status' => 'procurement']);
                }

                // Delete the LPO
                $lpo->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'LPO deleted successfully',
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete LPO: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Create LPO (Procurement role)
        Route::post('/', function (Request $request) {
            if ($request->user()->role !== 'procurement') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'requisition_id' => 'required|exists:requisitions,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'delivery_date' => 'nullable|date',
                'terms' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.requisition_item_id' => 'required|exists:requisition_items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.has_vat' => 'nullable|boolean',
            ]);

            DB::beginTransaction();
            try {
                // Generate LPO number
                $lpoNumber = 'LPO-' . strtoupper(uniqid()) . '-' . date('Ymd');

                // Calculate totals
                $subtotal = 0;
                $vatAmount = 0;
                foreach ($request->items as $item) {
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $itemTotal;
                    if ($item['has_vat'] ?? false) {
                        $vatAmount += $itemTotal * 0.18; // 18% VAT
                    }
                }
                $total = $subtotal + $vatAmount;

                $lpo = Lpo::create([
                    'lpo_number' => $lpoNumber,
                    'requisition_id' => $request->requisition_id,
                    'supplier_id' => $request->supplier_id,
                    'prepared_by' => $request->user()->id,
                    'delivery_date' => $request->delivery_date,
                    'terms' => $request->terms,
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'status' => 'draft', // Draft until CEO approves
                ]);

                // Create LPO items
                foreach ($request->items as $item) {
                    // Get requisition item for description and unit
                    $reqItem = RequisitionItem::find($item['requisition_item_id']);

                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $hasVat = $item['has_vat'] ?? false;
                    $vatRate = $item['vat_rate'] ?? 18.0;
                    $totalPrice = $hasVat ? ($itemTotal * (1 + $vatRate / 100)) : $itemTotal;

                    LpoItem::create([
                        'lpo_id' => $lpo->id,
                        'inventory_item_id' => $item['inventory_item_id'] ?? $reqItem->product_catalog_id,
                        'description' => $item['description'] ?? $reqItem->name,
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? $reqItem->unit,
                        'unit_price' => $item['unit_price'],
                        'total_price' => $totalPrice,
                        'has_vat' => $hasVat,
                        'vat_rate' => $vatRate,
                    ]);
                }

                // Update requisition status
                $requisition = Requisition::find($request->requisition_id);
                $requisition->update(['status' => 'lpo_issued']);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'LPO created successfully',
                    'data' => $lpo->load(['items']),
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create LPO: ' . $e->getMessage(),
                ], 500);
            }
        });

        // CEO Approve LPO
        Route::post('/{id}/approve', function (Request $request, $id) {
            if ($request->user()->role !== 'ceo') {
                return response()->json(['success' => false, 'message' => 'Access denied. CEO role required.'], 403);
            }

            $lpo = Lpo::find($id);
            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            if ($lpo->status !== 'draft' && $lpo->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Only draft or pending LPOs can be approved'], 400);
            }

            DB::beginTransaction();
            try {
                // Update LPO status to ceo_approved
                $lpo->update([
                    'status' => 'ceo_approved',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);

                // Update requisition status to CEO_APPROVED
                if ($lpo->requisition) {
                    $lpo->requisition->update(['status' => 'ceo_approved']);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'LPO approved successfully. Procurement can now issue to supplier.',
                    'data' => $lpo->fresh(['items', 'supplier', 'requisition.project', 'requisition.requester']),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve LPO: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Issue LPO to supplier (Procurement role)
        Route::post('/{id}/issue', function (Request $request, $id) {
            if ($request->user()->role !== 'procurement') {
                return response()->json(['success' => false, 'message' => 'Access denied. Procurement role required.'], 403);
            }

            $lpo = Lpo::with(['supplier', 'items'])->find($id);
            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            // Check LPO status for CEO approval
            if ($lpo->status !== 'ceo_approved') {
                return response()->json(['success' => false, 'message' => 'Only CEO-approved LPOs can be issued to supplier'], 400);
            }

            DB::beginTransaction();
            try {
                $lpo->update([
                    'status' => 'issued',
                    'issued_at' => now(),
                ]);

                // TODO: Send email to supplier with LPO details
                // Mail::to($lpo->supplier->email)->send(new LpoIssuedMail($lpo));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'LPO issued to supplier successfully',
                    'data' => $lpo->fresh(['items', 'supplier']),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to issue LPO: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Receive LPO delivery (Stores role)
        Route::post('/{id}/receive', function (Request $request, $id) {
            if ($request->user()->role !== 'stores') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'items' => 'required|array',
                'items.*.lpo_item_id' => 'required|exists:lpo_items,id',
                'items.*.received_quantity' => 'required|numeric|min:0',
                'items.*.condition' => 'nullable|string',
                'delivery_notes' => 'nullable|string',
            ]);

            $lpo = Lpo::with(['items', 'requisition'])->find($id);
            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            if ($lpo->status !== 'issued') {
                return response()->json(['success' => false, 'message' => 'Only issued LPOs can be received'], 400);
            }

            DB::beginTransaction();
            try {
                $requisition = $lpo->requisition;
                $project = $requisition->project;

                // Get the project's store
                $store = Store::where('project_id', $project->id)->first();

                if (!$store) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "No store found for project: {$project->name}"], 400);
                }

                foreach ($request->items as $itemData) {
                    $lpoItem = LpoItem::find($itemData['lpo_item_id']);
                    if (!$lpoItem) continue;

                    $receivedQty = $itemData['received_quantity'];
                    $condition = $itemData['condition'] ?? 'good';

                    if ($receivedQty > 0) {
                        // Record what was actually received
                        LpoReceivedItem::create([
                            'lpo_id' => $lpo->id,
                            'lpo_item_id' => $lpoItem->id,
                            'quantity_ordered' => $lpoItem->quantity,
                            'quantity_received' => $receivedQty,
                            'condition' => $condition,
                            'received_by' => $request->user()->id,
                        ]);

                        // Add to inventory - try to find existing item by product_catalog_id or name
                        $inventoryItem = null;

                        if ($lpoItem->product_catalog_id) {
                            $inventoryItem = InventoryItem::where('store_id', $store->id)
                                ->where('product_catalog_id', $lpoItem->product_catalog_id)
                                ->first();
                        }

                        if (!$inventoryItem) {
                            $inventoryItem = InventoryItem::where('store_id', $store->id)
                                ->where('name', $lpoItem->description)
                                ->first();
                        }

                        if ($inventoryItem) {
                            // Update existing item
                            $oldQuantity = $inventoryItem->quantity;
                            $newQuantity = $oldQuantity + $receivedQty;

                            $inventoryItem->update([
                                'quantity' => $newQuantity,
                                'unit_price' => $lpoItem->unit_price,
                            ]);
                        } else {
                            // Create new inventory item
                            $inventoryItem = InventoryItem::create([
                                'product_catalog_id' => $lpoItem->product_catalog_id,
                                'name' => $lpoItem->description,
                                'description' => $lpoItem->description,
                                'sku' => 'SKU-' . strtoupper(uniqid()),
                                'category' => 'General',
                                'unit_price' => $lpoItem->unit_price,
                                'unit' => $lpoItem->unit,
                                'quantity' => $receivedQty,
                                'reorder_level' => 10,
                                'track_per_project' => true,
                                'store_id' => $store->id,
                                'project_id' => $project->id,
                            ]);
                            $oldQuantity = 0;
                            $newQuantity = $receivedQty;
                        }

                        // Create inventory log
                        InventoryLog::create([
                            'inventory_item_id' => $inventoryItem->id,
                            'project_id' => $project->id,
                            'user_id' => $request->user()->id,
                            'type' => 'in',
                            'quantity' => $receivedQty,
                            'unit_price' => $lpoItem->unit_price,
                            'balance_after' => $newQuantity,
                            'notes' => "LPO Delivery: {$lpo->lpo_number} - {$lpoItem->description}" .
                                      ($condition !== 'good' ? " (Condition: {$condition})" : ""),
                        ]);
                    }
                }

                // Update LPO status to delivered
                $lpo->update([
                    'status' => 'delivered',
                    'delivery_date' => now(),
                    'delivery_notes' => $request->delivery_notes,
                ]);

                // Update requisition status
                $lpo->requisition->update(['status' => 'delivered']);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'LPO received successfully. Items added to inventory.',
                    'data' => $lpo->fresh(['receivedItems', 'requisition']),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to receive LPO: ' . $e->getMessage(),
                ], 500);
            }
        });
    });

    // ==================== SUPPLIERS ====================
    Route::prefix('suppliers')->group(function () {

        Route::get('/', function (Request $request) {
            $suppliers = Supplier::orderBy('name')->get();
            return response()->json(['success' => true, 'data' => $suppliers]);
        });

        Route::get('/{id}', function ($id) {
            $supplier = Supplier::with(['lpos'])->find($id);
            if (!$supplier) {
                return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
            }
            return response()->json(['success' => true, 'data' => $supplier]);
        });

        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['admin', 'procurement'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'category' => 'nullable|string|max:255',
            ]);

            $supplier = Supplier::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully',
                'data' => $supplier,
            ], 201);
        });

        Route::put('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['admin', 'procurement'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $supplier = Supplier::find($id);
            if (!$supplier) {
                return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'category' => 'nullable|string|max:255',
            ]);

            $supplier->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully',
                'data' => $supplier,
            ]);
        });
    });

    // ==================== INVENTORY & STORES ====================
    Route::prefix('inventory')->group(function () {

        // Get all stores (filtered by user's assigned projects for stores role)
        Route::get('/stores', function (Request $request) {
            $user = $request->user();
            $query = Store::query();

            // Filter by user's assigned projects for stores/store_manager role
            if (in_array($user->role, ['stores', 'store_manager'])) {
                $userProjectIds = DB::table('project_user')
                    ->where('user_id', $user->id)
                    ->pluck('project_id')
                    ->toArray();

                if (!empty($userProjectIds)) {
                    $query->whereIn('project_id', $userProjectIds);
                }
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            $stores = $query->with(['project'])->get();

            return response()->json(['success' => true, 'data' => $stores]);
        });

        // Get store items
        Route::get('/stores/{id}/items', function ($id) {
            $store = Store::with(['inventoryItems.productCatalog'])->find($id);

            if (!$store) {
                return response()->json(['success' => false, 'message' => 'Store not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'store' => $store,
                    'items' => $store->inventoryItems,
                ],
            ]);
        });

        // Search inventory items
        Route::get('/items/search', function (Request $request) {
            $user = $request->user();
            $query = InventoryItem::with(['store', 'productCatalog']);

            // Filter by store_id if provided
            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
            } else if ($user->role === 'stores') {
                // For stores users, filter by their assigned stores
                $userProjectIds = DB::table('project_user')
                    ->where('user_id', $user->id)
                    ->pluck('project_id');

                $storeIds = Store::whereIn('project_id', $userProjectIds)->pluck('id');

                // If user has assigned stores, filter by them
                if ($storeIds->isNotEmpty()) {
                    $query->whereIn('store_id', $storeIds);
                }
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('low_stock') && $request->low_stock) {
                $query->whereColumn('quantity', '<=', 'reorder_level');
            }

            $items = $query->orderBy('name')->get();

            return response()->json(['success' => true, 'data' => $items]);
        });

        // Get inventory logs
        Route::get('/logs', function (Request $request) {
            $query = InventoryLog::with(['inventoryItem', 'user']);

            if ($request->has('store_id')) {
                $query->whereHas('inventoryItem', function ($q) use ($request) {
                    $q->where('store_id', $request->store_id);
                });
            }

            $perPage = $request->get('per_page', 20);
            $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs->items(),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'total' => $logs->total(),
                ],
            ]);
        });

        // Create store release from requisition (for stores user)
        Route::post('/stores/{storeId}/releases', function (Request $request, $storeId) {
            if (!in_array($request->user()->role, ['stores', 'store_manager', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'requisition_id' => 'required|exists:requisitions,id',
                'items' => 'required|array|min:1',
                'items.*.requisition_item_id' => 'required|exists:requisition_items,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'notes' => 'nullable|string',
            ]);

            $requisition = Requisition::with(['items', 'project'])->find($request->requisition_id);

            if (!$requisition) {
                return response()->json(['success' => false, 'message' => 'Requisition not found'], 404);
            }

            if ($requisition->type !== 'store') {
                return response()->json(['success' => false, 'message' => 'Only store requisitions can be released'], 400);
            }

            if (!in_array($requisition->status, ['project_manager_approved', 'partial_release'])) {
                return response()->json(['success' => false, 'message' => 'Requisition is not ready for release'], 400);
            }

            // Get the store
            $store = Store::find($storeId);
            if (!$store) {
                return response()->json(['success' => false, 'message' => 'Store not found'], 404);
            }

            DB::beginTransaction();
            try {
                $totalRequestedQty = 0;
                $totalReleasedQty = 0;
                $releaseDetails = [];

                foreach ($request->items as $itemData) {
                    $reqItem = RequisitionItem::find($itemData['requisition_item_id']);
                    if (!$reqItem || $reqItem->requisition_id != $requisition->id) {
                        continue;
                    }

                    $releaseQty = floatval($itemData['quantity']);
                    $totalRequestedQty += $reqItem->quantity;

                    // Find inventory item - try exact name match first
                    $inventoryItem = InventoryItem::where('store_id', $storeId)
                        ->where('name', $reqItem->name)
                        ->first();

                    // Try case-insensitive exact match
                    if (!$inventoryItem) {
                        $inventoryItem = InventoryItem::where('store_id', $storeId)
                            ->whereRaw('LOWER(name) = ?', [strtolower($reqItem->name)])
                            ->first();
                    }

                    // Try LIKE match if exact match fails
                    if (!$inventoryItem) {
                        $inventoryItem = InventoryItem::where('store_id', $storeId)
                            ->where(function($q) use ($reqItem) {
                                $q->where('name', 'like', '%' . $reqItem->name . '%')
                                  ->orWhere('description', 'like', '%' . $reqItem->name . '%');
                            })
                            ->first();
                    }

                    // Try by product_catalog_id if still not found
                    if (!$inventoryItem && $reqItem->product_catalog_id) {
                        $inventoryItem = InventoryItem::where('store_id', $storeId)
                            ->where('product_catalog_id', $reqItem->product_catalog_id)
                            ->first();
                    }

                    if (!$inventoryItem) {
                        // Debug info to help troubleshoot
                        $availableItems = InventoryItem::where('store_id', $storeId)
                            ->pluck('name')
                            ->toArray();

                        $releaseDetails[] = [
                            'item' => $reqItem->name,
                            'requested' => $reqItem->quantity,
                            'released' => 0,
                            'status' => 'not_in_stock',
                            'debug' => [
                                'store_id' => $storeId,
                                'looking_for' => $reqItem->name,
                                'available_in_store' => $availableItems
                            ]
                        ];
                        continue;
                    }

                    // Check available stock
                    $availableQty = $inventoryItem->quantity;
                    $actualRelease = min($releaseQty, $availableQty, $reqItem->quantity);

                    if ($actualRelease <= 0) {
                        $releaseDetails[] = [
                            'item' => $reqItem->name,
                            'requested' => $reqItem->quantity,
                            'released' => 0,
                            'status' => 'insufficient_stock'
                        ];
                        continue;
                    }

                    // Deduct from inventory
                    $inventoryItem->quantity -= $actualRelease;
                    $inventoryItem->save();

                    $totalReleasedQty += $actualRelease;

                    // Log the inventory reduction
                    InventoryLog::create([
                        'inventory_item_id' => $inventoryItem->id,
                        'project_id' => $requisition->project_id,
                        'user_id' => $request->user()->id,
                        'type' => 'out',
                        'quantity' => $actualRelease,
                        'unit_price' => $inventoryItem->unit_price,
                        'balance_after' => $inventoryItem->quantity,
                        'notes' => "Store Release for Requisition: {$requisition->ref} - {$reqItem->name}",
                    ]);

                    // Update requisition item with released quantity
                    $reqItem->released_quantity = ($reqItem->released_quantity ?? 0) + $actualRelease;
                    $reqItem->save();

                    $releaseDetails[] = [
                        'item' => $reqItem->name,
                        'requested' => $reqItem->quantity,
                        'released' => $actualRelease,
                        'available' => $availableQty,
                        'status' => $actualRelease >= $reqItem->quantity ? 'complete' : 'partial'
                    ];
                }

                // Determine requisition status based on release
                $allItemsFullyReleased = true;
                foreach ($requisition->items as $item) {
                    $item->refresh();
                    if (($item->released_quantity ?? 0) < $item->quantity) {
                        $allItemsFullyReleased = false;
                        break;
                    }
                }

                if ($allItemsFullyReleased) {
                    $requisition->status = 'completed';
                } else if ($totalReleasedQty > 0) {
                    $requisition->status = 'partial_release';
                }
                $requisition->save();

                DB::commit();

                // If no items were released, return failure
                if ($totalReleasedQty == 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No items could be released. Check inventory availability.',
                        'data' => [
                            'requisition_status' => $requisition->status,
                            'total_requested' => $totalRequestedQty,
                            'total_released' => $totalReleasedQty,
                            'release_details' => $releaseDetails,
                        ],
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $allItemsFullyReleased
                        ? 'All items released successfully. Requisition completed.'
                        : 'Partial release completed. Some items may need additional releases.',
                    'data' => [
                        'requisition_status' => $requisition->status,
                        'total_requested' => $totalRequestedQty,
                        'total_released' => $totalReleasedQty,
                        'release_details' => $releaseDetails,
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process release: ' . $e->getMessage(),
                ], 500);
            }
        });
    });

    // ==================== STORE RELEASES ====================
    Route::prefix('store-releases')->group(function () {

        // Get all store releases
        Route::get('/', function (Request $request) {
            $query = StoreRelease::with(['store', 'project', 'requisition', 'items.inventoryItem', 'releasedBy']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by store
            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
            }

            // Filter by project
            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            $perPage = $request->get('per_page', 20);
            $releases = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $releases->items(),
                'meta' => [
                    'current_page' => $releases->currentPage(),
                    'last_page' => $releases->lastPage(),
                    'total' => $releases->total(),
                ],
            ]);
        });

        // Get single store release
        Route::get('/{id}', function ($id) {
            $release = StoreRelease::with(['store', 'project', 'requisition', 'items.inventoryItem', 'releasedBy'])->find($id);

            if (!$release) {
                return response()->json(['success' => false, 'message' => 'Store release not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $release]);
        });

        // Approve store release
        Route::post('/{id}/approve', function (Request $request, $id) {
            $release = StoreRelease::with('items.inventoryItem')->find($id);

            if (!$release) {
                return response()->json(['success' => false, 'message' => 'Store release not found'], 404);
            }

            if ($release->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Only pending releases can be approved'], 400);
            }

            DB::beginTransaction();
            try {
                // Reduce inventory stock for each item
                foreach ($release->items as $item) {
                    if ($item->inventoryItem) {
                        $inventoryItem = $item->inventoryItem;

                        // Check if sufficient stock
                        if ($inventoryItem->quantity < $item->quantity) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => "Insufficient stock for {$inventoryItem->name}. Available: {$inventoryItem->quantity}, Required: {$item->quantity}",
                            ], 400);
                        }

                        // Reduce stock
                        $inventoryItem->decrement('quantity', $item->quantity);

                        // Log the inventory reduction
                        InventoryLog::create([
                            'inventory_item_id' => $inventoryItem->id,
                            'type' => 'out',
                            'quantity' => $item->quantity,
                            'reference_type' => 'store_release',
                            'reference_id' => $release->id,
                            'notes' => "Released to {$release->project->name}",
                            'performed_by' => $request->user()->id,
                        ]);
                    }
                }

                // Update release status
                $release->update([
                    'status' => 'approved',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Store release approved and inventory updated successfully',
                    'data' => $release->fresh(['store', 'project', 'requisition', 'items.inventoryItem']),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve release: ' . $e->getMessage(),
                ], 500);
            }
        });
    });

    // ==================== PAYMENTS ====================
    Route::prefix('payments')->group(function () {

        // Get all payments
        Route::get('/', function (Request $request) {
            $query = Payment::with(['lpo.supplier', 'paidBy']);

            if ($request->has('status')) {
                // Special handling for CEO pending payments
                if ($request->status === 'pending_ceo') {
                    $query->where('status', 'pending')->where('ceo_approved', false);
                } else {
                    $query->where('status', $request->status);
                }
            }

            $perPage = $request->get('per_page', 20);
            $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'total' => $payments->total(),
                ],
            ]);
        });

        // Get pending requisitions for payment creation (delivered LPOs)
        // IMPORTANT: This must come BEFORE /{id} route to avoid route conflict
        Route::get('/pending-requisitions', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $pendingRequisitions = Requisition::where('status', 'delivered')
                ->whereHas('lpo', function($query) {
                    $query->where('status', 'delivered');
                })
                ->with(['project', 'lpo.supplier', 'lpo.items', 'lpo.receivedItems'])
                ->latest()
                ->get()
                ->map(function($requisition) {
                    // Calculate VAT-inclusive amount from LPO
                    $requisition->vat_inclusive_total = $requisition->lpo->total ?? $requisition->estimated_total;
                    $requisition->base_amount = $requisition->lpo->subtotal ?? $requisition->estimated_total;
                    $requisition->vat_amount = $requisition->lpo->vat_amount ?? 0;
                    return $requisition;
                });

            return response()->json([
                'success' => true,
                'data' => $pendingRequisitions,
            ]);
        });

        // Get single payment
        Route::get('/{id}', function ($id) {
            $payment = Payment::with(['lpo.supplier', 'paidBy'])->find($id);

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $payment]);
        });

        // Create payment (Finance role)
        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'requisition_id' => 'required|exists:requisitions,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:bank_transfer,cash,cheque,mobile_money',
                'payment_date' => 'required|date',
                'vat_amount' => 'required|numeric|min:0',
                'additional_costs' => 'nullable|numeric|min:0',
                'additional_costs_description' => 'nullable|string|max:255',
                'reference_number' => 'nullable|string|max:100',
                'notes' => 'nullable|string|max:1000',
            ]);

            $requisition = Requisition::with('lpo')->findOrFail($request->requisition_id);

            if ($requisition->status !== 'delivered') {
                return response()->json(['success' => false, 'message' => 'Only delivered requisitions can have payments created'], 400);
            }

            DB::beginTransaction();
            try {
                // Create payment record - pending CEO approval
                $payment = Payment::create([
                    'lpo_id' => $requisition->lpo->id,
                    'supplier_id' => $requisition->supplier_id ?? $requisition->lpo->supplier_id ?? null,
                    'paid_by' => $request->user()->id,
                    'payment_method' => $request->payment_method,
                    'status' => 'pending',
                    'amount' => $request->amount,
                    'paid_on' => $request->payment_date,
                    'reference' => $request->reference_number,
                    'notes' => $request->notes,
                    'vat_amount' => $request->vat_amount,
                    'additional_costs' => $request->additional_costs ?? 0,
                    'additional_costs_description' => $request->additional_costs_description,
                    'approval_status' => 'pending',
                ]);

                // Update requisition status
                $requisition->update(['status' => 'payment_completed']);

                // Create expense record
                Expense::create([
                    'project_id' => $requisition->project_id,
                    'type' => 'supplier_payment',
                    'description' => 'Supplier Payment: ' . ($requisition->lpo->supplier->name ?? 'Unknown') . ' - ' . $requisition->ref,
                    'amount' => $request->amount,
                    'incurred_on' => $request->payment_date,
                    'recorded_by' => $request->user()->id,
                    'status' => 'pending',
                    'notes' => $request->notes . " | Payment Ref: " . ($request->reference_number ?? 'N/A'),
                    'reference_id' => $payment->id,
                    'reference_type' => Payment::class,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment created successfully and pending CEO approval',
                    'data' => $payment->fresh(['lpo.supplier', 'paidBy']),
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment: ' . $e->getMessage(),
                ], 500);
            }
        });

        // Approve/Reject payment (CEO role)
        Route::post('/{id}/approve', function (Request $request, $id) {
            if ($request->user()->role !== 'ceo') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'action' => 'required|in:approve,reject',
                'comments' => 'nullable|string',
            ]);

            $payment = Payment::find($id);
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            $status = $request->action === 'approve' ? 'ceo_approved' : 'ceo_rejected';
            $payment->update([
                'status' => $status,
                'approval_comments' => $request->comments,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment ' . $request->action . 'd successfully',
                'data' => $payment,
            ]);
        });
    });

    // ==================== EXPENSES ====================
    Route::prefix('expenses')->group(function () {

        Route::get('/', function (Request $request) {
            $query = Expense::with(['project', 'recordedBy']);

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $perPage = $request->get('per_page', 20);
            $expenses = $query->orderBy('incurred_on', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $expenses->items(),
                'meta' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'total' => $expenses->total(),
                ],
            ]);
        });

        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'type' => 'required|string|max:255',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'notes' => 'nullable|string',
                'reference' => 'nullable|string',
            ]);

            $expense = Expense::create([
                'project_id' => $request->project_id,
                'type' => $request->type,
                'description' => $request->description,
                'amount' => $request->amount,
                'incurred_on' => $request->date,
                'notes' => $request->notes,
                'recorded_by' => $request->user()->id,
                'status' => 'unpaid',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense recorded successfully',
                'data' => $expense->load('project'),
            ], 201);
        });

        // Update expense
        Route::put('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json(['success' => false, 'message' => 'Expense not found'], 404);
            }

            $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'type' => 'required|string|max:255',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'notes' => 'nullable|string',
                'reference' => 'nullable|string',
            ]);

            $expense->update([
                'project_id' => $request->project_id,
                'type' => $request->type,
                'description' => $request->description,
                'amount' => $request->amount,
                'incurred_on' => $request->date,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense updated successfully',
                'data' => $expense->load('project'),
            ]);
        });

        // Delete expense
        Route::delete('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $expense = Expense::find($id);
            if (!$expense) {
                return response()->json(['success' => false, 'message' => 'Expense not found'], 404);
            }

            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expense deleted successfully',
            ]);
        });
    });

    // ==================== PRODUCT CATALOG ====================
    Route::prefix('products')->group(function () {

        Route::get('/', function (Request $request) {
            $query = ProductCatalog::with('category')->whereNull('deleted_at');

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('name')->get();

            return response()->json(['success' => true, 'data' => $products]);
        });

        Route::get('/categories', function () {
            $categories = ProductCategory::orderBy('name')->get();
            return response()->json(['success' => true, 'data' => $categories]);
        });
        
        // Create product (Admin only)
        Route::post('/', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255|unique:product_catalogs,sku',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:product_categories,id',
                'unit' => 'required|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);
            
            $product = ProductCatalog::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Product added to catalog successfully',
                'data' => $product->load('category'),
            ], 201);
        });
        
        // Update product (Admin only)
        Route::put('/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $product = ProductCatalog::find($id);
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }
            
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'sku' => 'nullable|string|max:255|unique:product_catalogs,sku,' . $id,
                'description' => 'nullable|string',
                'category_id' => 'sometimes|required|exists:product_categories,id',
                'unit' => 'sometimes|required|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);
            
            $product->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load('category'),
            ]);
        });
        
        // Delete product (Admin only)
        Route::delete('/{id}', function (Request $request, $id) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $product = ProductCatalog::find($id);
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }
            
            // Check if product is in use before deleting
            if (!$product->canBeDeleted()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot delete product as it is already being used in requisitions or inventory'
                ], 400);
            }
            
            $product->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Product removed from catalog successfully',
            ]);
        });
        
        // Create category (Admin only)
        Route::post('/categories', function (Request $request) {
            if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $request->validate([
                'name' => 'required|string|max:255|unique:product_categories,name',
                'description' => 'nullable|string',
            ]);
            
            $category = ProductCategory::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);
        });
    });

    // ==================== MILESTONES ====================
    Route::prefix('milestones')->group(function () {

        Route::get('/', function (Request $request) {
            $query = ProjectMilestone::with('project');

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $milestones = $query->orderBy('due_date')->get();

            return response()->json(['success' => true, 'data' => $milestones]);
        });

        Route::get('/{id}', function ($id) {
            $milestone = ProjectMilestone::with('project')->find($id);

            if (!$milestone) {
                return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
            }

            $data = $milestone->toArray();
            // Add full photo URL if photo exists
            if ($milestone->photo_path) {
                $data['photo_url'] = asset('storage/' . $milestone->photo_path);
            }

            return response()->json(['success' => true, 'data' => $data]);
        });

        // Create new milestone (PM/Admin)
        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['surveyor', 'admin', 'project_manager'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'due_date' => 'required|date',
                'cost_estimate' => 'nullable|numeric|min:0',
            ]);
            
            $milestone = ProjectMilestone::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Milestone created successfully',
                'data' => $milestone,
            ], 201);
        });

        // Update milestone details (PM/Admin)
        Route::put('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['admin', 'project_manager'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $milestone = ProjectMilestone::find($id);
            if (!$milestone) {
                return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
            }
            
            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'due_date' => 'sometimes|required|date',
                'cost_estimate' => 'nullable|numeric|min:0',
                'status' => 'nullable|in:pending,in_progress,completed,delayed',
                'completion_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
            
            $milestone->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Milestone updated successfully',
                'data' => $milestone,
            ]);
        });
        
        // Delete milestone (PM/Admin)
        Route::delete('/{id}', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['admin', 'project_manager'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $milestone = ProjectMilestone::find($id);
            if (!$milestone) {
                return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
            }
            
            // Delete photo if exists
            if ($milestone->photo_path && Storage::disk('public')->exists($milestone->photo_path)) {
                Storage::disk('public')->delete($milestone->photo_path);
            }
            
            $milestone->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Milestone deleted successfully',
            ]);
        });

        // Create new milestone (Surveyor role) - Keep for backward compatibility if needed, but the root POST / matches it.
        // Re-routing /create to the same logic or leaving it if surveyor uses it specifically.
        Route::post('/create', function (Request $request) {

            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'due_date' => 'required|date',
                'cost_estimate' => 'nullable|numeric|min:0',
            ]);

            $milestoneData = [
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'cost_estimate' => $request->cost_estimate,
                'status' => 'pending',
                'completion_percentage' => 0,
            ];

            $milestone = ProjectMilestone::create($milestoneData);

            return response()->json([
                'success' => true,
                'message' => 'Milestone created successfully',
                'data' => $milestone,
            ]);
        });

        // Update milestone progress (Surveyor role)
        Route::post('/{id}/update-progress', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['surveyor', 'admin', 'project_manager'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'progress_percentage' => 'nullable|numeric|min:0|max:100',
                'status' => 'nullable|in:pending,in_progress,completed,delayed',
                'notes' => 'nullable|string',
                'actual_cost' => 'nullable|numeric|min:0',
                'photo_caption' => 'nullable|string',
            ]);

            $milestone = ProjectMilestone::find($id);
            if (!$milestone) {
                return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
            }

            $updateData = [];
            if ($request->has('progress_percentage')) {
                $updateData['completion_percentage'] = $request->progress_percentage;
            }
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                if ($request->status === 'completed') {
                    $updateData['completed_at'] = now();
                }
            }
            if ($request->has('notes')) {
                $updateData['progress_notes'] = $request->notes;
            }
            if ($request->has('actual_cost')) {
                $updateData['actual_cost'] = $request->actual_cost;
            }
            if ($request->has('photo_caption')) {
                $updateData['photo_caption'] = $request->photo_caption;
            }

            if (!empty($updateData)) {
                $milestone->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Milestone updated successfully',
                'data' => $milestone->fresh(),
            ]);
        });

        // Upload milestone photo
        Route::post('/{id}/photo', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['surveyor', 'admin', 'project_manager'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'photo' => 'required|image|max:5120',
                'caption' => 'nullable|string|max:500',
            ]);

            $milestone = ProjectMilestone::find($id);
            if (!$milestone) {
                return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
            }

            // Delete old photo if exists
            if ($milestone->photo_path && Storage::disk('public')->exists($milestone->photo_path)) {
                Storage::disk('public')->delete($milestone->photo_path);
            }

            // Store new photo
            $path = $request->file('photo')->store('milestone-photos', 'public');

            $milestone->update([
                'photo_path' => $path,
                'photo_caption' => $request->caption,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'data' => [
                    'photo_path' => $path,
                    'photo_url' => asset('storage/' . $path),
                ],
            ]);
        });

        // Delete milestone photo
        Route::delete('/{id}/photo', function (Request $request, $id) {
            if (!in_array($request->user()->role, ['surveyor', 'admin', 'project_manager'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $milestone = ProjectMilestone::find($id);
            if (!$milestone) {
                return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
            }

            if ($milestone->photo_path && Storage::disk('public')->exists($milestone->photo_path)) {
                Storage::disk('public')->delete($milestone->photo_path);
            }

            $milestone->update([
                'photo_path' => null,
                'photo_caption' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully',
            ]);
        });
    });

    // ==================== LABOR MANAGEMENT ====================
    Route::prefix('labor')->group(function () {

        // Get all labor workers (root endpoint)
        Route::get('/', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $workers = LaborWorker::with(['payments'])->orderBy('name')->get();

            // Transform for Flutter
            $data = $workers->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'phone' => $w->phone,
                    'email' => $w->email,
                    'id_number' => $w->id_number,
                    'nssf_number' => $w->nssf_number,
                    'bank_name' => $w->bank_name,
                    'bank_account' => $w->bank_account,
                    'role' => $w->role,
                    'payment_frequency' => $w->payment_frequency,
                    'daily_rate' => $w->daily_rate,
                    'monthly_rate' => $w->monthly_rate,
                    'is_active' => $w->status === 'active' ? 1 : 0,
                    'total_paid' => $w->payments->sum('amount'),
                    'payments_count' => $w->payments->count(),
                    'created_at' => $w->created_at,
                    'updated_at' => $w->updated_at,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        });

        // Get all labor workers (alternative endpoint)
        Route::get('/workers', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $workers = LaborWorker::with(['payments'])->orderBy('name')->get();

            // Transform for Flutter
            $data = $workers->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'phone' => $w->phone,
                    'email' => $w->email,
                    'id_number' => $w->id_number,
                    'nssf_number' => $w->nssf_number,
                    'bank_name' => $w->bank_name,
                    'bank_account' => $w->bank_account,
                    'role' => $w->role,
                    'payment_frequency' => $w->payment_frequency,
                    'daily_rate' => $w->daily_rate,
                    'monthly_rate' => $w->monthly_rate,
                    'is_active' => $w->status === 'active' ? 1 : 0,
                    'total_paid' => $w->payments->sum('amount'),
                    'payments_count' => $w->payments->count(),
                    'created_at' => $w->created_at,
                    'updated_at' => $w->updated_at,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        });

        // Get labor payments
        Route::get('/payments', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $query = LaborPayment::with(['worker', 'paidBy']);

            if ($request->has('worker_id')) {
                $query->where('labor_worker_id', $request->worker_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 20);
            $payments = $query->orderBy('payment_date', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'total' => $payments->total(),
                ],
            ]);
        });

        // Create worker (POST to /workers subroute)
        Route::post('/workers', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255',
                'role' => 'required|string|max:255',
                'payment_frequency' => 'required|in:daily,weekly,monthly',
            ]);

            // Get default project if not provided
            $projectId = $request->project_id;
            if (!$projectId) {
                $defaultProject = \App\Models\Project::first();
                $projectId = $defaultProject ? $defaultProject->id : 1;
            }

            $data = [
                'project_id' => $projectId,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'id_number' => $request->id_number,
                'nssf_number' => $request->nssf_number,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'role' => $request->role,
                'daily_rate' => $request->daily_rate ?? 0,
                'monthly_rate' => $request->monthly_rate ?? 0,
                'payment_frequency' => $request->payment_frequency,
                'start_date' => now()->toDateString(),
                'status' => 'active',
                'created_by' => $request->user()->id,
            ];

            $worker = LaborWorker::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Worker created successfully',
                'data' => $worker,
            ], 201);
        });

        // Create worker (POST to root - for Flutter compatibility)
        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255',
                'role' => 'required|string|max:255',
                'payment_frequency' => 'required|in:daily,weekly,monthly',
            ]);

            // Get default project if not provided
            $projectId = $request->project_id;
            if (!$projectId) {
                $defaultProject = \App\Models\Project::first();
                $projectId = $defaultProject ? $defaultProject->id : 1;
            }

            $data = [
                'project_id' => $projectId,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'id_number' => $request->id_number,
                'nssf_number' => $request->nssf_number,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'role' => $request->role,
                'daily_rate' => $request->daily_rate ?? 0,
                'monthly_rate' => $request->monthly_rate ?? 0,
                'payment_frequency' => $request->payment_frequency,
                'start_date' => now()->toDateString(),
                'status' => 'active',
                'created_by' => $request->user()->id,
            ];

            $worker = LaborWorker::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Worker created successfully',
                'data' => $worker,
            ], 201);
        });

        // Create labor payment for worker
        Route::post('/{workerId}/payments', function (Request $request, $workerId) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $worker = LaborWorker::find($workerId);
            if (!$worker) {
                return response()->json(['success' => false, 'message' => 'Worker not found'], 404);
            }

            $request->validate([
                'days_worked' => 'required|integer|min:1',
                'gross_amount' => 'required|numeric|min:0',
                'nssf_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'required|string',
                'period_start' => 'required|date',
                'period_end' => 'required|date',
                'description' => 'required|string',
            ]);

            $grossAmount = $request->gross_amount;
            $nssfAmount = $request->nssf_amount ?? ($grossAmount * 0.1);
            $netAmount = $grossAmount - $nssfAmount;

            // Generate payment reference
            $paymentRef = 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $payment = LaborPayment::create([
                'labor_worker_id' => $workerId,
                'payment_reference' => $paymentRef,
                'payment_date' => $request->payment_date ?? now()->toDateString(),
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'days_worked' => $request->days_worked,
                'gross_amount' => $grossAmount,
                'nssf_amount' => $nssfAmount,
                'net_amount' => $netAmount,
                'amount' => $netAmount,
                'description' => $request->description,
                'notes' => $request->notes,
                'payment_method' => $request->payment_method,
                'paid_by' => $request->user()->id,
                'status' => 'paid',
            ]);

            // Create expense record
            Expense::create([
                'project_id' => $worker->project_id,
                'type' => 'Labor',
                'amount' => $netAmount,
                'incurred_on' => $request->payment_date ?? now()->toDateString(),
                'description' => "Labor payment for {$worker->name}: {$request->description}",
                'labor_payment_id' => $payment->id,
                'recorded_by' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment,
            ], 201);
        });
    });

    // ==================== SUBCONTRACTORS ====================
    Route::prefix('subcontractors')->group(function () {

        Route::get('/', function () {
            $subcontractors = Subcontractor::with(['projectContracts.payments'])->orderBy('name')->get();

            // Transform to Flutter expected format with totals
            $data = $subcontractors->map(function ($s) {
                $totalContractValue = $s->projectContracts ? $s->projectContracts->sum('contract_amount') : 0;
                $totalPaid = $s->projectContracts ? $s->projectContracts->sum(function ($c) {
                    return $c->payments ? $c->payments->sum('amount') : 0;
                }) : 0;

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'company_name' => $s->contact_person,
                    'phone' => $s->phone,
                    'email' => $s->email,
                    'specialization' => $s->specialization,
                    'tin_number' => $s->tax_number,
                    'address' => $s->address,
                    'is_active' => $s->status === 'active' ? 1 : 0,
                    'contracts_count' => $s->projectContracts ? $s->projectContracts->count() : 0,
                    'total_contract_value' => $totalContractValue,
                    'total_paid' => $totalPaid,
                    'created_at' => $s->created_at,
                    'updated_at' => $s->updated_at,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        });

        Route::get('/{id}', function ($id) {
            $subcontractor = Subcontractor::with(['projectContracts.project', 'projectContracts.payments'])->find($id);

            if (!$subcontractor) {
                return response()->json(['success' => false, 'message' => 'Subcontractor not found'], 404);
            }

            // Transform contracts with project names and totals
            $contracts = $subcontractor->projectContracts->map(function ($contract) {
                $totalPaid = $contract->payments ? $contract->payments->sum('amount') : 0;
                return [
                    'id' => $contract->id,
                    'project_id' => $contract->project_id,
                    'project_name' => $contract->project->name ?? 'Unknown Project',
                    'contract_number' => $contract->contract_number,
                    'contract_amount' => $contract->contract_amount,
                    'work_description' => $contract->work_description,
                    'terms' => $contract->terms,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'status' => $contract->status,
                    'total_paid' => $totalPaid,
                    'balance' => $contract->contract_amount - $totalPaid,
                    'created_at' => $contract->created_at,
                ];
            });

            $totalContractValue = $subcontractor->projectContracts->sum('contract_amount');
            $totalPaid = $subcontractor->projectContracts->sum(function ($c) {
                return $c->payments ? $c->payments->sum('amount') : 0;
            });

            // Transform to Flutter expected format
            $data = [
                'id' => $subcontractor->id,
                'name' => $subcontractor->name,
                'company_name' => $subcontractor->contact_person,
                'phone' => $subcontractor->phone,
                'email' => $subcontractor->email,
                'specialization' => $subcontractor->specialization,
                'tin_number' => $subcontractor->tax_number,
                'address' => $subcontractor->address,
                'is_active' => $subcontractor->status === 'active' ? 1 : 0,
                'contracts_count' => $subcontractor->projectContracts->count(),
                'total_contract_value' => $totalContractValue,
                'total_paid' => $totalPaid,
                'created_at' => $subcontractor->created_at,
                'updated_at' => $subcontractor->updated_at,
                'contracts' => $contracts,
            ];

            return response()->json(['success' => true, 'data' => $data]);
        });

        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['admin', 'finance'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'specialization' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'tin_number' => 'nullable|string|max:255',
                'tax_number' => 'nullable|string|max:255',
            ]);

            // Map Flutter fields to database fields
            $data = [
                'name' => $request->name,
                'contact_person' => $request->company_name ?? $request->contact_person,
                'phone' => $request->phone,
                'email' => $request->email,
                'specialization' => $request->specialization ?? 'General',
                'address' => $request->address,
                'tax_number' => $request->tin_number ?? $request->tax_number,
                'status' => 'active',
            ];

            $subcontractor = Subcontractor::create($data);

            // Return transformed data for Flutter
            $responseData = [
                'id' => $subcontractor->id,
                'name' => $subcontractor->name,
                'company_name' => $subcontractor->contact_person,
                'phone' => $subcontractor->phone,
                'email' => $subcontractor->email,
                'specialization' => $subcontractor->specialization,
                'tin_number' => $subcontractor->tax_number,
                'address' => $subcontractor->address,
                'is_active' => 1,
                'contracts_count' => 0,
                'total_contract_value' => 0,
                'total_paid' => 0,
                'created_at' => $subcontractor->created_at,
                'updated_at' => $subcontractor->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Subcontractor created successfully',
                'data' => $responseData,
            ], 201);
        });

        // Create contract for subcontractor
        Route::post('/{subcontractorId}/contracts', function (Request $request, $subcontractorId) {
            if (!in_array($request->user()->role, ['admin', 'finance'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $subcontractor = Subcontractor::find($subcontractorId);
            if (!$subcontractor) {
                return response()->json(['success' => false, 'message' => 'Subcontractor not found'], 404);
            }

            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'contract_amount' => 'required|numeric|min:0',
                'work_description' => 'required|string',
                'start_date' => 'required|date',
            ]);

            // Generate contract number
            $contractNumber = 'CNT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $contract = ProjectSubcontractor::create([
                'subcontractor_id' => $subcontractorId,
                'project_id' => $request->project_id,
                'contract_number' => $contractNumber,
                'contract_amount' => $request->contract_amount,
                'work_description' => $request->work_description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'terms' => $request->terms,
                'status' => 'active',
            ]);

            // Load project name
            $contract->load('project');

            return response()->json([
                'success' => true,
                'message' => 'Contract created successfully',
                'data' => array_merge($contract->toArray(), [
                    'project_name' => $contract->project->name ?? 'Unknown',
                    'total_paid' => 0,
                ]),
            ], 201);
        });

        // Record payment for contract
        Route::post('/contract/{contractId}/payments', function (Request $request, $contractId) {
            if (!in_array($request->user()->role, ['admin', 'finance'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $contract = ProjectSubcontractor::with(['subcontractor', 'project'])->find($contractId);
            if (!$contract) {
                return response()->json(['success' => false, 'message' => 'Contract not found'], 404);
            }

            $request->validate([
                'amount' => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'payment_type' => 'required|string',
                'payment_method' => 'required|string',
                'description' => 'required|string',
            ]);

            // Check balance
            $totalPaid = SubcontractorPayment::where('project_subcontractor_id', $contractId)->sum('amount');
            $balance = $contract->contract_amount - $totalPaid;

            if ($request->amount > $balance) {
                return response()->json(['success' => false, 'message' => 'Payment amount exceeds balance'], 422);
            }

            // Generate payment reference
            $paymentRef = 'SUB-PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $payment = SubcontractorPayment::create([
                'project_subcontractor_id' => $contractId,
                'payment_reference' => $paymentRef,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'description' => $request->description,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'paid_by' => $request->user()->id,
            ]);

            // Create expense record
            Expense::create([
                'project_id' => $contract->project_id,
                'type' => 'Subcontractor',
                'amount' => $request->amount,
                'incurred_on' => $request->payment_date,
                'description' => "Subcontractor payment to {$contract->subcontractor->name}: {$request->description}",
                'subcontractor_payment_id' => $payment->id,
                'recorded_by' => $request->user()->id,
            ]);

            // Update contract status if fully paid
            $newTotalPaid = $totalPaid + $request->amount;
            if ($newTotalPaid >= $contract->contract_amount) {
                $contract->update(['status' => 'completed']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment,
            ], 201);
        });

        // Get contract ledger
        Route::get('/contract/{contractId}/ledger', function ($contractId) {
            $contract = ProjectSubcontractor::with(['subcontractor', 'project', 'payments' => function($q) {
                $q->orderBy('payment_date', 'asc');
            }])->find($contractId);

            if (!$contract) {
                return response()->json(['success' => false, 'message' => 'Contract not found'], 404);
            }

            $totalPaid = $contract->payments->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'contract' => array_merge($contract->toArray(), [
                        'project_name' => $contract->project->name ?? 'Unknown',
                        'subcontractor_name' => $contract->subcontractor->name ?? 'Unknown',
                        'total_paid' => $totalPaid,
                        'balance' => $contract->contract_amount - $totalPaid,
                    ]),
                    'payments' => $contract->payments,
                ],
            ]);
        });
    });

    // ==================== NOTIFICATIONS ====================
    Route::prefix('notifications')->group(function () {

        Route::get('/', function (Request $request) {
            $notifications = InAppNotification::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();

            return response()->json(['success' => true, 'data' => $notifications]);
        });

        Route::post('/{id}/read', function (Request $request, $id) {
            $notification = InAppNotification::where('user_id', $request->user()->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
            }

            $notification->update(['read_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        });

        Route::post('/mark-all-read', function (Request $request) {
            InAppNotification::where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
        });
    });

    // ==================== DEVICE TOKENS (Push Notifications) ====================
    Route::prefix('device-token')->group(function () {

        // Register device token for push notifications
        Route::post('/register', function (Request $request) {
            $request->validate([
                'device_token' => 'required|string',
                'device_type' => 'required|in:ios,android,web',
            ]);

            $user = $request->user();

            // Check if token already exists for any user
            $existingToken = DeviceToken::where('device_token', $request->device_token)->first();

            if ($existingToken) {
                // If token belongs to a different user, reassign it
                if ($existingToken->tokenable_id !== $user->id || $existingToken->tokenable_type !== get_class($user)) {
                    $existingToken->update([
                        'tokenable_type' => get_class($user),
                        'tokenable_id' => $user->id,
                        'device_type' => $request->device_type,
                        'is_active' => true,
                        'last_used_at' => now(),
                    ]);
                } else {
                    // Just update the existing token
                    $existingToken->update([
                        'is_active' => true,
                        'last_used_at' => now(),
                    ]);
                }
            } else {
                // Create new token
                DeviceToken::create([
                    'tokenable_type' => get_class($user),
                    'tokenable_id' => $user->id,
                    'device_token' => $request->device_token,
                    'device_type' => $request->device_type,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Device token registered successfully',
            ]);
        });

        // Unregister device token (on logout)
        Route::post('/unregister', function (Request $request) {
            $request->validate([
                'device_token' => 'required|string',
            ]);

            $user = $request->user();

            // Find and deactivate the token
            DeviceToken::where('device_token', $request->device_token)
                ->where('tokenable_id', $user->id)
                ->where('tokenable_type', get_class($user))
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Device token unregistered successfully',
            ]);
        });
    });

    // ==================== REPORTS ====================
    Route::prefix('reports')->group(function () {

        // Staff reports
        Route::get('/staff', function (Request $request) {
            $query = StaffReport::with('project');

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('report_type')) {
                $query->where('report_type', $request->report_type);
            }

            $perPage = $request->get('per_page', 20);
            $reports = $query->orderBy('date', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reports->items(),
                'meta' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'total' => $reports->total(),
                ],
            ]);
        });

        // QHSE reports
        Route::get('/qhse', function (Request $request) {
            $query = QhseReport::query();

            if ($request->has('report_type')) {
                $query->where('report_type', $request->report_type);
            }

            if ($request->has('department')) {
                $query->where('department', $request->department);
            }

            if ($request->has('location')) {
                $query->where('location', $request->location);
            }

            $perPage = $request->get('per_page', 20);
            $reports = $query->orderBy('report_date', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reports->items(),
                'meta' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'total' => $reports->total(),
                ],
            ]);
        });
    });

    // ==================== EQUIPMENTS ====================
    Route::prefix('equipments')->group(function () {
        
        // Get all equipments
        Route::get('/', function (Request $request) {
            $user = $request->user();
            $query = Equipment::with(['project', 'addedBy']);
            
            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by category
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            $equipments = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $equipments,
            ]);
        });

        // Get single equipment
        Route::get('/{id}', function ($id) {
            $equipment = Equipment::with(['project', 'addedBy'])->find($id);
            
            if (!$equipment) {
                return response()->json(['success' => false, 'message' => 'Equipment not found'], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $equipment,
            ]);
        });

        // Add equipment
        Route::post('/', function (Request $request) {
            $user = $request->user();
            
            // Allow Admin and Finance to add
            if (!in_array($user->role, ['admin', 'finance', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }
            
            $request->validate([
                'name' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'category' => 'required|string',
                'description' => 'nullable|string',
                'serial_number' => 'nullable|string',
                'condition' => 'required|in:new,good,fair,poor,needs_repair',
                'location' => 'nullable|string',
                'value' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'status' => 'required|in:active,inactive,maintenance,disposed',
                'project_id' => 'nullable|exists:projects,id',
            ]);
            
            $data = $request->except('images');
            $data['added_by'] = $user->id;
            
            // Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                     // Store in storage/app/public/equipments
                    $path = $image->store('equipments', 'public');
                    // We store just the path (e.g. 'equipments/filename.jpg')
                    // The mobile app handles appending base URL
                    $imagePaths[] = $path;
                }
            } else if ($request->images && is_array($request->images)) {
                // Handle base64 if needed, but Flutter is sending files
            }
            
            $data['images'] = $imagePaths;

            $equipment = Equipment::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Equipment added successfully',
                'data' => $equipment,
            ], 201);
        });

        // Update equipment
        Route::put('/{id}', function (Request $request, $id) {
            $user = $request->user();
            
             // Allow Admin and Finance to update
             if (!in_array($user->role, ['admin', 'finance', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $equipment = Equipment::find($id);
            if (!$equipment) {
                return response()->json(['success' => false, 'message' => 'Equipment not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'model' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string',
                'description' => 'nullable|string',
                'serial_number' => 'nullable|string',
                'condition' => 'sometimes|required|in:new,good,fair,poor,needs_repair',
                'location' => 'nullable|string',
                'value' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'status' => 'sometimes|required|in:active,inactive,maintenance,disposed',
                'project_id' => 'nullable|exists:projects,id',
            ]);

            $data = $request->except(['images', '_method']);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imagePaths = $equipment->images ?? [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('equipments', 'public');
                    $imagePaths[] = $path;
                }
                $data['images'] = $imagePaths; // Append new images to existing
            }

            $equipment->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Equipment updated successfully',
                'data' => $equipment,
            ]);
        });
        
        // Delete equipment
        Route::delete('/{id}', function (Request $request, $id) {
             if ($request->user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Access denied. Only Admin can delete equipment.'], 403);
            }
            
            $equipment = Equipment::find($id);
            if (!$equipment) {
                return response()->json(['success' => false, 'message' => 'Equipment not found'], 404);
            }
            
            $equipment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Equipment deleted successfully',
            ]);
        });

    // ====================
    // CLIENT ROUTES
    // ====================

    Route::prefix('client')->group(function () {
        // Client Login (Public - no auth required)
        Route::post('/login', function (Request $request) {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $client = \App\Models\Client::where('email', $request->email)->first();

            if (!$client || !Hash::check($request->password, $client->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            if (!$client->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact administrator.',
                ], 403);
            }

            $token = $client->createToken('client-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'company' => $client->company,
                        'is_active' => $client->is_active,
                        'created_at' => $client->created_at,
                    ],
                    'token' => $token,
                ],
            ]);
        });

        // Protected client routes
        Route::middleware('auth:sanctum')->group(function () {
            // Logout
            Route::post('/logout', function (Request $request) {
                $request->user()->currentAccessToken()->delete();
                return response()->json(['success' => true, 'message' => 'Logged out successfully']);
            });

            // Get client profile
            Route::get('/profile', function (Request $request) {
                $client = $request->user();
                $client->load('projects');

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'company' => $client->company,
                        'is_active' => $client->is_active,
                        'projects_count' => $client->projects->count(),
                        'created_at' => $client->created_at,
                    ],
                ]);
            });

            // Get client projects
            Route::get('/projects', function (Request $request) {
                $client = $request->user();
                $projects = $client->projects()
                    ->with(['users', 'milestones'])
                    ->get()
                    ->map(function ($project) {
                        return [
                            'id' => $project->id,
                            'name' => $project->name,
                            'code' => $project->code,
                            'location' => $project->location,
                            'status' => $project->status,
                            'budget' => $project->budget,
                            'start_date' => $project->start_date,
                            'end_date' => $project->end_date,
                            'description' => $project->description,
                            'progress' => $project->progress ?? 0,
                            'milestones_count' => $project->milestones->count(),
                            'completed_milestones' => $project->milestones->where('status', 'completed')->count(),
                            'created_at' => $project->created_at,
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'data' => $projects,
                ]);
            });

            // Get project milestones
            Route::get('/projects/{projectId}/milestones', function (Request $request, $projectId) {
                $client = $request->user();
                $project = $client->projects()->find($projectId);

                if (!$project) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Project not found or access denied',
                    ], 404);
                }

                $milestones = $project->milestones()
                    ->orderBy('scheduled_date', 'asc')
                    ->get()
                    ->map(function ($milestone) {
                        $photos = [];
                        if ($milestone->photos) {
                            $photoArray = json_decode($milestone->photos, true);
                            if (is_array($photoArray)) {
                                foreach ($photoArray as $photo) {
                                    $photos[] = asset('storage/' . $photo);
                                }
                            }
                        }

                        return [
                            'id' => $milestone->id,
                            'title' => $milestone->title,
                            'description' => $milestone->description,
                            'status' => $milestone->status,
                            'scheduled_date' => $milestone->scheduled_date,
                            'completed_date' => $milestone->completed_date,
                            'progress_percentage' => $milestone->progress_percentage ?? 0,
                            'notes' => $milestone->notes,
                            'surveyor_name' => $milestone->surveyor_name,
                            'photos' => $photos,
                            'created_at' => $milestone->created_at,
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'data' => $milestones,
                ]);
            });

            // Get milestone detail
            Route::get('/projects/{projectId}/milestones/{milestoneId}', function (Request $request, $projectId, $milestoneId) {
                $client = $request->user();
                $project = $client->projects()->find($projectId);

                if (!$project) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Project not found or access denied',
                    ], 404);
                }

                $milestone = $project->milestones()->find($milestoneId);

                if (!$milestone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Milestone not found',
                    ], 404);
                }

                $photos = [];
                if ($milestone->photos) {
                    $photoArray = json_decode($milestone->photos, true);
                    if (is_array($photoArray)) {
                        foreach ($photoArray as $photo) {
                            $photos[] = asset('storage/' . $photo);
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $milestone->id,
                        'project_id' => $milestone->project_id,
                        'project_name' => $project->name,
                        'title' => $milestone->title,
                        'description' => $milestone->description,
                        'status' => $milestone->status,
                        'scheduled_date' => $milestone->scheduled_date,
                        'completed_date' => $milestone->completed_date,
                        'progress_percentage' => $milestone->progress_percentage ?? 0,
                        'notes' => $milestone->notes,
                        'surveyor_name' => $milestone->surveyor_name,
                        'photos' => $photos,
                        'created_at' => $milestone->created_at,
                        'updated_at' => $milestone->updated_at,
                    ],
                ]);
            });
        });
    });
});
});