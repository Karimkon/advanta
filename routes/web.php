<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// ----------------------
// Dashboard Controllers
// ----------------------
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProjectController; 
use App\Http\Controllers\Operations\OperationsDashboardController;
use App\Http\Controllers\Procurement\ProcurementDashboardController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Stores\StoresDashboardController;
use App\Http\Controllers\CEO\CEODashboardController;
use App\Http\Controllers\ProjectManager\ProjectManagerDashboardController;
use App\Http\Controllers\SiteManager\SiteManagerDashboardController;
use App\Http\Controllers\Supplier\SupplierDashboardController;
use App\Http\Controllers\Admin\AdminRequisitionController;
use App\Http\Controllers\ProjectManager\ProjectManagerRequisitionController;
use App\Http\Controllers\Admin\AdminProcurementController;        
use App\Http\Controllers\Admin\AdminLpoController;
use App\Http\Controllers\Admin\AdminFinanceController;    
use App\Http\Controllers\Operations\OperationsRequisitionController;    
use App\Http\Controllers\Procurement\ProcurementRequisitionController;
use App\Http\Controllers\Procurement\ProcurementLpoController;
use App\Http\Controllers\Procurement\ProcurementSupplierController;
use App\Http\Controllers\CEO\CEORequisitionController;
use App\Http\Controllers\Stores\StoreInventoryController; 
use App\Http\Controllers\Stores\StoreReleaseController;
use App\Http\Controllers\Engineer\EngineerDashboardController;
use App\Http\Controllers\Engineer\EngineerRequisitionController;    
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\FinancialReportsController;   
use App\Http\Controllers\CEO\CEOFinancialReportsController;     
use App\Http\Controllers\Stores\StockMovementController; 
use App\Http\Controllers\CEO\CEOInventoryController;   
use App\Http\Controllers\Stores\StoreLpoController;
use App\Http\Controllers\Surveyor\SurveyorDashboardController;
use App\Http\Controllers\Surveyor\SurveyorMilestoneController;
use App\Http\Controllers\CEO\CEOMilestoneController;
use App\Http\Controllers\Admin\AdminMilestoneController;
use App\Http\Controllers\Surveyor\SurveyorProjectController;
use App\Http\Controllers\ProjectManager\ProjectManagerMilestoneController;  
use App\Http\Controllers\CEO\CEOLpoController;
use App\Http\Controllers\StaffReportController;

// ----------------------
// Landing Page
// ----------------------
Route::get('/', fn () => view('welcome'))->name('welcome');
Route::get('/manual', fn () => view('manual'))->name('manual');    

// ====================================================
// LOGIN VIEWS PER ROLE
// ====================================================
Route::get('/admin/login', fn() => view('admin.auth.login'))->name('admin.login');
Route::get('/operations/login', fn() => view('operations.auth.login'))->name('operations.login');
Route::get('/procurement/login', fn() => view('procurement.auth.login'))->name('procurement.login');
Route::get('/finance/login', fn() => view('finance.auth.login'))->name('finance.login');
Route::get('/stores/login', fn() => view('stores.auth.login'))->name('stores.login');
Route::get('/ceo/login', fn() => view('ceo.auth.login'))->name('ceo.login');
Route::get('/project/login', fn() => view('project_manager.auth.login'))->name('project_manager.login');
Route::get('/engineer/login', fn() => view('engineer.auth.login'))->name('engineer.login');
Route::get('/supplier/login', fn() => view('supplier.auth.login'))->name('supplier.login');
Route::get('/surveyor/login', fn() => view('surveyor.auth.login'))->name('surveyor.login');

// ====================================================
// LOGIN SUBMIT PER ROLE (standard pattern)
// ====================================================

function roleLogin(Request $request, string $role, string $redirect)
{
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => $role
    ], $request->boolean('remember'))) {

        $request->session()->regenerate();
        return redirect()->intended(route($redirect));
    }

    return back()->with('error', "Only $role accounts can login here.");
}

// ADMIN
Route::post('/admin/login', fn(Request $r) => 
    roleLogin($r,'admin','admin.dashboard')
)->name('admin.login.submit');

// OPERATIONS
Route::post('/operations/login', fn(Request $r) => 
    roleLogin($r,'operations','operations.dashboard')
)->name('operations.login.submit');

// PROCUREMENT
Route::post('/procurement/login', fn(Request $r) => 
    roleLogin($r,'procurement','procurement.dashboard')
)->name('procurement.login.submit');

// FINANCE
Route::post('/finance/login', fn(Request $r) => 
    roleLogin($r,'finance','finance.dashboard')
)->name('finance.login.submit');

// STORES
Route::post('/stores/login', fn(Request $r) => 
    roleLogin($r,'stores','stores.dashboard')
)->name('stores.login.submit');

// CEO
Route::post('/ceo/login', fn(Request $r) => 
    roleLogin($r,'ceo','ceo.dashboard')
)->name('ceo.login.submit');

// PROJECT MANAGER
Route::post('/project/login', fn(Request $r) => 
    roleLogin($r,'project_manager','project_manager.dashboard')
)->name('project_manager.login.submit');

Route::post('/engineer/login', fn(Request $r) => 
    roleLogin($r,'engineer','engineer.dashboard')
)->name('engineer.login.submit');

Route::post('/surveyor/login', fn(Request $r) => 
    roleLogin($r,'surveyor','surveyor.dashboard')
)->name('surveyor.login.submit');

// SUPPLIER
Route::post('/supplier/login', fn(Request $r) => 
    roleLogin($r,'supplier','supplier.dashboard')
)->name('supplier.login.submit');

// ====================================================
// LOGOUT (shared)
// ====================================================
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ====================================================
// DASHBOARD ROUTES PER ROLE
// ====================================================

// ADMIN
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class,'index'])->name('dashboard');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy'); 

    // Projects Management
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [AdminProjectController::class, 'index'])->name('index');
        Route::get('/create', [AdminProjectController::class, 'create'])->name('create');
        Route::post('/', [AdminProjectController::class, 'store'])->name('store');
        Route::get('/{project}', [AdminProjectController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [AdminProjectController::class, 'edit'])->name('edit');
        Route::put('/{project}', [AdminProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [AdminProjectController::class, 'destroy'])->name('destroy');
    });

    // Requisitions Management
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [AdminRequisitionController::class, 'index'])->name('index');
        Route::get('/create', [AdminRequisitionController::class, 'create'])->name('create');
        Route::post('/', [AdminRequisitionController::class, 'store'])->name('store');
        Route::get('/{requisition}', [AdminRequisitionController::class, 'show'])->name('show');
        Route::get('/{requisition}/edit', [AdminRequisitionController::class, 'edit'])->name('edit');
        Route::put('/{requisition}', [AdminRequisitionController::class, 'update'])->name('update');
        Route::delete('/{requisition}', [AdminRequisitionController::class, 'destroy'])->name('destroy');
    });

     Route::post('/{requisition}/approve', [AdminRequisitionController::class, 'approve'])->name('approve');
    Route::post('/{requisition}/reject', [AdminRequisitionController::class, 'reject'])->name('reject');
    Route::post('/{requisition}/send-to-procurement', [AdminRequisitionController::class, 'sendToProcurement'])->name('send-to-procurement');

    // Procurement
    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::get('/', function () {
            return view('admin.procurement.index');
        })->name('index');
    });

    // Suppliers Management
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', function () {
            return view('admin.suppliers.index');
        })->name('index');
        Route::get('/create', function () {
            return view('admin.suppliers.create');
        })->name('create');
        Route::post('/', function (Request $request) {
            return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created');
        })->name('store');
    });

    // LPOs Management
    Route::prefix('lpos')->name('lpos.')->group(function () {
        Route::get('/', function () {
            return view('admin.lpos.index');
        })->name('index');
        Route::get('/create', function () {
            return view('admin.lpos.create');
        })->name('create');
        Route::post('/', function (Request $request) {
            return redirect()->route('admin.lpos.index')->with('success', 'LPO created');
        })->name('store');
    });

    // Finance
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', function () {
            return view('admin.finance.index');
        })->name('index');
    });

    // Payments Management
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', function () {
            return view('admin.payments.index');
        })->name('index');
    });

    // Stores & Inventory
Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('/', function () {
        $stores = \App\Models\Store::with('project')->get();
        return view('admin.stores.index', compact('stores'));
    })->name('index');
});

// Inventory Management
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', function () {
        return view('admin.inventory.index');
    })->name('index');
});

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('admin.reports.index');
        })->name('index');
    });

    // ADMIN Milestone Routes - Full CRUD
Route::prefix('milestones')->name('milestones.')->group(function () {
    Route::get('/', [AdminMilestoneController::class, 'index'])->name('index');
    Route::get('/project/{project}', [AdminMilestoneController::class, 'projectMilestones'])->name('project');
    Route::get('/project/{project}/create', [AdminMilestoneController::class, 'create'])->name('create');
    Route::post('/project/{project}', [AdminMilestoneController::class, 'store'])->name('store');
    Route::get('/project/{project}/milestone/{milestone}', [AdminMilestoneController::class, 'show'])->name('show');
    Route::get('/project/{project}/milestone/{milestone}/edit', [AdminMilestoneController::class, 'edit'])->name('edit');
    Route::put('/project/{project}/milestone/{milestone}', [AdminMilestoneController::class, 'update'])->name('update');
    Route::delete('/project/{project}/milestone/{milestone}', [AdminMilestoneController::class, 'destroy'])->name('destroy');
    Route::delete('/project/{project}/milestone/{milestone}/photo', [AdminMilestoneController::class, 'removePhoto'])->name('remove-photo');
});
});

// API Routes for dynamic store inventory loading
Route::get('/api/stores/{store}/inventory', function ($storeId) {
    $store = \App\Models\Store::find($storeId);
    
    if (!$store) {
        return response()->json(['error' => 'Store not found'], 404);
    }
    
    $items = $store->inventoryItems()
        ->where('quantity', '>', 0)
        ->get(['name', 'quantity', 'unit', 'unit_price']);
    
    return response()->json(['items' => $items]);
})->name('api.store.inventory');

// OPERATIONS
Route::middleware(['auth','role:operations'])->prefix('operations')->name('operations.')->group(function () {
    Route::get('/dashboard', [OperationsDashboardController::class,'index'])->name('dashboard');
    
    // Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [OperationsRequisitionController::class, 'index'])->name('index');
        Route::get('/pending', [OperationsRequisitionController::class, 'pending'])->name('pending');
        Route::get('/approved', [OperationsRequisitionController::class, 'approved'])->name('approved');
        Route::get('/{requisition}', [OperationsRequisitionController::class, 'show'])->name('show');
         Route::get('/{requisition}/edit', [OperationsRequisitionController::class, 'edit'])->name('edit');
    Route::put('/{requisition}', [OperationsRequisitionController::class, 'update'])->name('update');
        Route::post('/{requisition}/approve', [OperationsRequisitionController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [OperationsRequisitionController::class, 'reject'])->name('reject');
        Route::post('/{requisition}/send-to-procurement', [OperationsRequisitionController::class, 'sendToProcurement'])->name('send-to-procurement');
    });
});

// PROCUREMENT
Route::middleware(['auth','role:procurement'])->prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/dashboard', [ProcurementDashboardController::class,'index'])->name('dashboard');
    
    // Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [ProcurementRequisitionController::class, 'index'])->name('index');
        Route::get('/pending', [ProcurementRequisitionController::class, 'pending'])->name('pending');
        Route::get('/in-procurement', [ProcurementRequisitionController::class, 'inProcurement'])->name('in-procurement');
        Route::get('/{requisition}', [ProcurementRequisitionController::class, 'show'])->name('show');
        Route::get('/{requisition}/edit', [ProcurementRequisitionController::class, 'edit'])->name('edit');
    Route::put('/{requisition}', [ProcurementRequisitionController::class, 'update'])->name('update'); 
        Route::post('/{requisition}/start-procurement', [ProcurementRequisitionController::class, 'startProcurement'])->name('start-procurement');
        Route::post('/{requisition}/send-to-ceo', [ProcurementRequisitionController::class, 'sendToCEO'])->name('send-to-ceo');
        Route::post('/{requisition}/create-lpo', [ProcurementRequisitionController::class, 'createLpo'])->name('create-lpo');
        Route::get('/{requisition}/create-lpo-page', [ProcurementRequisitionController::class, 'showCreateLpoPage'])
                ->name('create-lpo-page');
    });
    
    // LPOs
    Route::prefix('lpos')->name('lpos.')->group(function () {
        Route::get('/', [ProcurementLpoController::class, 'index'])->name('index');
        Route::get('/{lpo}', [ProcurementLpoController::class, 'show'])->name('show');
        Route::post('/{lpo}/issue', [ProcurementRequisitionController::class, 'issueLpo'])->name('issue');
        Route::post('/{lpo}/mark-delivered', [ProcurementLpoController::class, 'markDelivered'])->name('mark-delivered');
    });
    
     // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [ProcurementSupplierController::class, 'index'])->name('index');
        Route::get('/create', [ProcurementSupplierController::class, 'create'])->name('create');
        Route::post('/', [ProcurementSupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}', [ProcurementSupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [ProcurementSupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [ProcurementSupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [ProcurementSupplierController::class, 'destroy'])->name('destroy');
    });
});

// Add to web.php temporarily
Route::get('/debug/store-service/{lpo_id}', function($lpo_id) {
    $lpo = \App\Models\Lpo::with(['items', 'requisition.project'])->find($lpo_id);
    
    if (!$lpo) {
        return response()->json(['error' => 'LPO not found']);
    }
    
    \Log::info('=== DEBUG STORE SERVICE ===');
    \Log::info('LPO Details:', [
        'lpo_id' => $lpo->id,
        'project_id' => $lpo->requisition->project_id,
        'project_name' => $lpo->requisition->project->name,
        'items_count' => $lpo->items->count(),
        'items' => $lpo->items->map(function($item) {
            return [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price
            ];
        })
    ]);
    
    // Test the store service
    try {
        $storeService = new \App\Services\StoreService();
        $result = $storeService->processDeliveredLpo($lpo);
        
        return response()->json([
            'success' => true,
            'lpo' => [
                'id' => $lpo->id,
                'project_id' => $lpo->requisition->project_id,
                'items_count' => $lpo->items->count()
            ],
            'store_service_result' => $result,
            'message' => 'Store service executed successfully'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// FINANCE
Route::middleware(['auth','role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [FinanceDashboardController::class,'index'])->name('dashboard');
    
    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/pending', [PaymentController::class, 'pending'])->name('pending');
        Route::get('/create/{requisition}', [PaymentController::class, 'create'])->name('create');
        Route::post('/store/{requisition}', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('/export/csv', [PaymentController::class, 'export'])->name('export');
    });
    
    // Expenses
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::get('/reports/summary', [ExpenseController::class, 'reports'])->name('reports');
        Route::get('/export/csv', [ExpenseController::class, 'export'])->name('export');
    });
    
    // Financial Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [FinancialReportsController::class, 'index'])->name('index');
        Route::get('/project/{project}', [FinancialReportsController::class, 'projectReport'])->name('project');
        Route::get('/export/financial-summary', [FinancialReportsController::class, 'exportFinancialSummary'])->name('export.summary');
    });
});

// STORES
Route::middleware(['auth','role:stores'])->prefix('stores')->name('stores.')->group(function () {
    Route::get('/dashboard', [StoresDashboardController::class,'index'])->name('dashboard');
    
    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/{store}', [StoreInventoryController::class, 'index'])->name('index');
        Route::get('/{store}/create', [StoreInventoryController::class, 'create'])->name('create');
        Route::post('/{store}', [StoreInventoryController::class, 'store'])->name('store');
        Route::get('/{store}/{inventoryItem}', [StoreInventoryController::class, 'show'])->name('show');
        Route::post('/{store}/{inventoryItem}/adjust', [StoreInventoryController::class, 'adjustStock'])->name('adjust');
    });
    
    // Store Releases
    Route::prefix('releases')->name('releases.')->group(function () {
        Route::get('/{store}', [StoreReleaseController::class, 'index'])->name('index');
        Route::get('/{store}/requisition/{requisition}/create', [StoreReleaseController::class, 'create'])->name('create');
        Route::post('/{store}/requisition/{requisition}', [StoreReleaseController::class, 'store'])->name('store');
        Route::get('/{store}/{release}', [StoreReleaseController::class, 'show'])->name('show');
    });
    

     // Stock Movements 
    Route::prefix('movements')->name('movements.')->group(function () {
        Route::get('/{store}', [StockMovementController::class, 'index'])->name('index');
        Route::get('/{store}/filter', [StockMovementController::class, 'filter'])->name('filter');
        Route::get('/{store}/export', [StockMovementController::class, 'export'])->name('export');
    });

    Route::prefix('lpos')->name('lpos.')->group(function () {
    Route::get('/{store}', [StoreLpoController::class, 'index'])->name('index');
    Route::get('/{store}/delivered', [StoreLpoController::class, 'delivered'])->name('delivered');
    Route::get('/{store}/{lpo}', [StoreLpoController::class, 'show'])->name('show');
    Route::get('/{store}/{lpo}/confirm-delivery', [StoreLpoController::class, 'confirmDelivery'])->name('confirm-delivery');
    Route::post('/{store}/{lpo}/process-delivery', [StoreLpoController::class, 'processDelivery'])->name('process-delivery');
});

});

// CEO
Route::middleware(['auth','role:ceo'])->prefix('ceo')->name('ceo.')->group(function () {
    Route::get('/dashboard', [CEODashboardController::class,'index'])->name('dashboard');
    
    // Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [CEORequisitionController::class, 'index'])->name('index');
        Route::get('/pending', [CEORequisitionController::class, 'pending'])->name('pending');
        Route::get('/{requisition}', [CEORequisitionController::class, 'show'])->name('show');
         Route::get('/{requisition}/edit', [CEORequisitionController::class, 'edit'])->name('edit');
        Route::put('/{requisition}', [CEORequisitionController::class, 'update'])->name('update'); 
        Route::post('/{requisition}/approve', [CEORequisitionController::class, 'approveRequisition'])->name('approve');
        Route::post('/{requisition}/reject', [CEORequisitionController::class, 'rejectRequisition'])->name('reject');
        Route::post('/lpos/{lpo}/approve', [CEORequisitionController::class, 'approveLpo'])->name('lpos.approve');
    });
    
    // LPOs
    Route::prefix('lpos')->name('lpos.')->group(function () {
    Route::get('/', [CEOLpoController::class, 'index'])->name('index');
    Route::get('/{lpo}', [CEOLpoController::class, 'show'])->name('show');
});
    
    // Financial Reports - ADD THESE NEW ROUTES
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [CEOFinancialReportsController::class, 'index'])->name('index');
        Route::get('/financial', [CEOFinancialReportsController::class, 'index'])->name('financial');
        Route::get('/project/{project}', [CEOFinancialReportsController::class, 'projectReport'])->name('project');
        Route::get('/requisitions', [CEOFinancialReportsController::class, 'requisitionsReport'])->name('requisitions');
        Route::get('/export/summary', [CEOFinancialReportsController::class, 'exportFinancialSummary'])->name('export.summary');
    });

      // Inventory Overview - ADD THESE NEW ROUTES
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [CEOInventoryController::class, 'index'])->name('index');
        Route::get('/store/{store}', [CEOInventoryController::class, 'storeDetail'])->name('store');
        Route::get('/movements', [CEOInventoryController::class, 'stockMovements'])->name('movements');
        Route::get('/export', [CEOInventoryController::class, 'exportInventoryReport'])->name('export');
    });

    // CEO Milestone Routes
    Route::prefix('milestones')->name('milestones.')->group(function () {
        Route::get('/', [CEOMilestoneController::class, 'index'])->name('index');
        Route::get('/project/{project}', [CEOMilestoneController::class, 'projectMilestones'])->name('project');
        Route::get('/project/{project}/milestone/{milestone}', [CEOMilestoneController::class, 'show'])->name('show');
    });
});

// PROJECT MANAGER
Route::middleware(['auth','role:project_manager'])->prefix('project-manager')->name('project_manager.')->group(function () {
    Route::get('/dashboard', [ProjectManagerDashboardController::class,'index'])->name('dashboard');
    
    // Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [ProjectManagerRequisitionController::class, 'index'])->name('index');
        Route::get('/create', [ProjectManagerRequisitionController::class, 'create'])->name('create');
        Route::post('/', [ProjectManagerRequisitionController::class, 'store'])->name('store');
        Route::get('/pending', [ProjectManagerRequisitionController::class, 'pending'])->name('pending'); // MOVED HERE - BEFORE {requisition}
        Route::get('/{requisition}', [ProjectManagerRequisitionController::class, 'show'])->name('show');
        Route::get('/{requisition}/edit', [ProjectManagerRequisitionController::class, 'edit'])->name('edit'); // NEW
    Route::put('/{requisition}', [ProjectManagerRequisitionController::class, 'update'])->name('update'); // NEW
        Route::post('/{requisition}/approve', [ProjectManagerRequisitionController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [ProjectManagerRequisitionController::class, 'reject'])->name('reject');
        Route::post('/{requisition}/send-to-store', [ProjectManagerRequisitionController::class, 'sendToStore'])->name('send-to-store');
    });
    
    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', function () {
            return view('project_manager.projects.index');
        })->name('index');
    });
});

// ENGINEER
Route::middleware(['auth','role:engineer'])->prefix('engineer')->name('engineer.')->group(function () {
    Route::get('/dashboard', [EngineerDashboardController::class,'index'])->name('dashboard');
    
    // Requisitions
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        Route::get('/', [EngineerRequisitionController::class, 'index'])->name('index');
        Route::get('/create', [EngineerRequisitionController::class, 'create'])->name('create');
        Route::post('/', [EngineerRequisitionController::class, 'store'])->name('store');
        Route::get('/{requisition}', [EngineerRequisitionController::class, 'show'])->name('show');
        Route::get('/pending', [EngineerRequisitionController::class, 'pending'])->name('pending');
    });
    
    // Projects
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [EngineerProjectController::class, 'index'])->name('index');
    });
});

// SURVEYOR
Route::middleware(['auth','role:surveyor'])->prefix('surveyor')->name('surveyor.')->group(function () {
    Route::get('/dashboard', [SurveyorDashboardController::class,'index'])->name('dashboard');
     Route::get('/projects', [SurveyorProjectController::class, 'index'])->name('projects.index');
      Route::get('/milestones', [SurveyorMilestoneController::class, 'allMilestones'])->name('milestones.index');
    // Milestones Management
    Route::prefix('projects/{project}/milestones')->name('milestones.')->group(function () {
        Route::get('/', [SurveyorMilestoneController::class, 'index'])->name('index');
        Route::get('/{milestone}', [SurveyorMilestoneController::class, 'show'])->name('show');
        Route::get('/{milestone}/edit', [SurveyorMilestoneController::class, 'edit'])->name('edit');
        Route::put('/{milestone}', [SurveyorMilestoneController::class, 'update'])->name('update');
         Route::delete('/{milestone}/photo', [SurveyorMilestoneController::class, 'removePhoto'])->name('remove-photo');
    });
});

// Staff Reports Public Routes (no authentication required)
Route::prefix('staff-reports')->name('staff-reports.')->group(function () {
    Route::get('/submit', [StaffReportController::class, 'create'])->name('create');
    Route::post('/submit', [StaffReportController::class, 'store'])->name('store');
    Route::get('/success', [StaffReportController::class, 'success'])->name('success');
});

// Staff Reports Admin/CEO Routes (protected)
Route::middleware(['auth', 'role:admin,ceo'])->prefix('staff-reports')->name('staff-reports.')->group(function () {
    Route::get('/', [StaffReportController::class, 'index'])->name('index');
    Route::get('/{staffReport}', [StaffReportController::class, 'show'])->name('show');
    Route::delete('/{staffReport}', [StaffReportController::class, 'destroy'])->name('destroy');
    Route::get('/{staffReport}/download/{index}', [StaffReportController::class, 'downloadAttachment'])->name('download.attachment');
});

// SUPPLIER
Route::middleware(['auth','role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierDashboardController::class,'index'])->name('dashboard');
});