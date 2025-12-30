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
        'reporter_name' => 'required|string|max:255',
        'reporter_email' => 'nullable|email',
        'report_type' => 'required|in:daily,weekly',
        'project_id' => 'required|exists:projects,id',
        'date' => 'required|date',
        'content' => 'required|string',
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
        'reporter_name' => $request->reporter_name,
        'reporter_email' => $request->reporter_email,
        'report_type' => $request->report_type,
        'project_id' => $request->project_id,
        'date' => $request->date,
        'content' => $request->content,
        'attachments' => json_encode($attachments),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Report submitted successfully',
        'data' => $report,
    ], 201);
});

// QHSE Reports - Public submission with access code
Route::post('/public/qhse-reports', function (Request $request) {
    $request->validate([
        'access_code' => 'required|string',
        'reporter_name' => 'required|string|max:255',
        'reporter_email' => 'nullable|email',
        'report_type' => 'required|in:safety,quality,health,environment,incident,companydocuments',
        'project_id' => 'required|exists:projects,id',
        'incident_date' => 'required|date',
        'description' => 'required|string',
        'severity' => 'nullable|in:low,medium,high,critical',
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
        'reporter_name' => $request->reporter_name,
        'reporter_email' => $request->reporter_email,
        'report_type' => $request->report_type,
        'project_id' => $request->project_id,
        'incident_date' => $request->incident_date,
        'description' => $request->description,
        'severity' => $request->severity ?? 'medium',
        'attachments' => json_encode($attachments),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'QHSE report submitted successfully',
        'data' => $report,
    ], 201);
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
                    'pending_payments' => Payment::where('status', 'pending_ceo')->count(),
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
                $stats = [
                    'total_stores' => Store::count(),
                    'total_inventory_items' => InventoryItem::count(),
                    'low_stock_items' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')->count(),
                    'pending_releases' => StoreRelease::where('status', 'pending')->count(),
                    'pending_lpo_deliveries' => Lpo::where('status', 'issued')->count(),
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

            // Filter by user's assigned projects for non-admin/ceo roles
            if (!in_array($user->role, ['admin', 'ceo'])) {
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
                    $query->where('type', 'store')
                        ->whereIn('status', ['approved', 'completed']);
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
            $query = Lpo::with(['supplier', 'requisition', 'items']);

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
            $lpo = Lpo::with(['supplier', 'requisition', 'items', 'receivedItems'])->find($id);

            if (!$lpo) {
                return response()->json(['success' => false, 'message' => 'LPO not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $lpo,
            ]);
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
                    'issued_by' => $request->user()->id,
                    'delivery_date' => $request->delivery_date,
                    'terms' => $request->terms,
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'status' => 'pending',
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

            if ($lpo->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Only pending LPOs can be approved'], 400);
            }

            try {
                $lpo->update(['status' => 'ceo_approved']);

                return response()->json([
                    'success' => true,
                    'message' => 'LPO approved successfully',
                    'data' => $lpo->fresh(['items', 'supplier']),
                ]);
            } catch (\Exception $e) {
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

            if ($lpo->status !== 'ceo_approved') {
                return response()->json(['success' => false, 'message' => 'Only CEO approved LPOs can be issued'], 400);
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

        // Get all stores
        Route::get('/stores', function (Request $request) {
            $query = Store::query();

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
            $query = InventoryItem::with(['store', 'productCatalog']);

            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
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
                $query->where('status', $request->status);
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
                'category' => 'required|string|max:255',
                'description' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'receipt' => 'nullable|file|max:5120',
            ]);

            $receipt = null;
            if ($request->hasFile('receipt')) {
                $receipt = $request->file('receipt')->store('expense-receipts', 'public');
            }

            $expense = Expense::create([
                'project_id' => $request->project_id,
                'category' => $request->category,
                'description' => $request->description,
                'amount' => $request->amount,
                'date' => $request->date,
                'receipt' => $receipt,
                'created_by' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense recorded successfully',
                'data' => $expense,
            ], 201);
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

        // Create new milestone (Surveyor role)
        Route::post('/create', function (Request $request) {
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

            $workers = LaborWorker::with(['latestPayments' => function($query) {
                $query->latest()->take(5);
            }])->orderBy('name')->get();

            return response()->json(['success' => true, 'data' => $workers]);
        });

        // Get all labor workers (alternative endpoint)
        Route::get('/workers', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $workers = LaborWorker::orderBy('name')->get();
            return response()->json(['success' => true, 'data' => $workers]);
        });

        // Get labor payments
        Route::get('/payments', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin', 'ceo'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $query = LaborPayment::with(['worker', 'paidBy']);

            if ($request->has('worker_id')) {
                $query->where('worker_id', $request->worker_id);
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

        // Create worker
        Route::post('/workers', function (Request $request) {
            if (!in_array($request->user()->role, ['finance', 'admin'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255',
                'id_number' => 'nullable|string|max:255',
                'rate_type' => 'required|in:daily,monthly',
                'rate' => 'required|numeric|min:0',
                'bank_account' => 'nullable|string|max:255',
                'nssf_number' => 'nullable|string|max:255',
            ]);

            $worker = LaborWorker::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Worker created successfully',
                'data' => $worker,
            ], 201);
        });
    });

    // ==================== SUBCONTRACTORS ====================
    Route::prefix('subcontractors')->group(function () {

        Route::get('/', function () {
            $subcontractors = Subcontractor::orderBy('name')->get();
            return response()->json(['success' => true, 'data' => $subcontractors]);
        });

        Route::get('/{id}', function ($id) {
            $subcontractor = Subcontractor::with(['projectContracts', 'payments'])->find($id);

            if (!$subcontractor) {
                return response()->json(['success' => false, 'message' => 'Subcontractor not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $subcontractor]);
        });

        Route::post('/', function (Request $request) {
            if (!in_array($request->user()->role, ['admin', 'finance'])) {
                return response()->json(['success' => false, 'message' => 'Access denied'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'address' => 'nullable|string',
            ]);

            $subcontractor = Subcontractor::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Subcontractor created successfully',
                'data' => $subcontractor,
            ], 201);
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
            $query = QhseReport::with('project');

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('report_type')) {
                $query->where('report_type', $request->report_type);
            }

            if ($request->has('severity')) {
                $query->where('severity', $request->severity);
            }

            $perPage = $request->get('per_page', 20);
            $reports = $query->orderBy('incident_date', 'desc')->paginate($perPage);

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
});
