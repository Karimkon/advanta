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
use App\Http\Controllers\Finance\SubcontractorController;
use App\Http\Controllers\Finance\LaborController;
use App\Http\Controllers\Finance\SubcontractorPaymentController;
use App\Http\Controllers\Engineer\EngineerProjectController;
use App\Http\Controllers\ProjectManager\ProjectManagerProjectController;
use App\Http\Controllers\CEO\CEOPaymentController;
use App\Http\Controllers\CEO\CEOStaffReportController;           
use App\Http\Controllers\Admin\ProductCategoryController;    
use App\Http\Controllers\Admin\ProductCatalogController; 
use App\Http\Controllers\QhseReportController;     
use App\Http\Controllers\CEO\CEOQhseReportController;               

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
// routes/web.php (admin section)
    Route::resource('product-categories', ProductCategoryController::class); 
    
    // Product Catalog Routes
Route::get('product-catalog/export', [ProductCatalogController::class, 'export'])->name('product-catalog.export');
Route::get('product-catalog/export-data', [ProductCatalogController::class, 'exportData'])->name('product-catalog.export-data');
    Route::resource('product-catalog', \App\Http\Controllers\Admin\ProductCatalogController::class);
    Route::get('product-catalog/search', [\App\Http\Controllers\Admin\ProductCatalogController::class, 'search'])->name('product-catalog.search');
    Route::post('product-catalog/bulk-import', [\App\Http\Controllers\Admin\ProductCatalogController::class, 'bulkImport'])->name('product-catalog.bulk-import');
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
            $lpos = \App\Models\Lpo::with(['supplier', 'requisition.project', 'issuer'])
                ->orderBy('created_at', 'desc')
                ->get();
            return view('admin.lpos.index', compact('lpos'));
        })->name('index');

        Route::get('/{lpo}', function (\App\Models\Lpo $lpo) {
            $lpo->load(['supplier', 'requisition.project', 'items', 'issuer', 'receivedItems']);
            return view('admin.lpos.show', compact('lpo'));
        })->name('show');

        Route::get('/create', function () {
            return view('admin.lpos.create');
        })->name('create');

        Route::post('/', function (Request $request) {
            return redirect()->route('admin.lpos.index')->with('success', 'LPO created');
        })->name('store');

        // LPO Fix routes
        Route::get('/{lpo}/fix', [\App\Http\Controllers\Admin\AdminLpoController::class, 'fix'])->name('fix');
        Route::post('/{lpo}/fix-status', [\App\Http\Controllers\Admin\AdminLpoController::class, 'fixStatus'])->name('fix-status');
        Route::post('/{lpo}/update-prices', [\App\Http\Controllers\Admin\AdminLpoController::class, 'updatePrices'])->name('update-prices');
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

    // Office Staff Management (Admin)
    Route::resource('office-staff', \App\Http\Controllers\Finance\OfficeStaffController::class);
    Route::get('office-staff/{officeStaff}/pay', [\App\Http\Controllers\Finance\OfficeStaffController::class, 'createPayment'])->name('office-staff.create-payment');
    
    // Salary Payments (Admin)
    Route::post('salary-payments', [\App\Http\Controllers\Finance\SalaryPaymentController::class, 'store'])->name('salary-payments.store');
    Route::delete('salary-payments/{payment}', [\App\Http\Controllers\Finance\SalaryPaymentController::class, 'destroy'])->name('salary-payments.destroy');

    // Stores & Inventory
Route::prefix('stores')->name('stores.')->group(function () {
    Route::get('/', function () {
        // Only fetch project stores (exclude main stores)
        $stores = \App\Models\Store::with(['project.users', 'inventoryItems'])
            ->where('type', 'project')
            ->get();
        $totalInventoryItems = \App\Models\InventoryItem::whereIn('store_id', $stores->pluck('id'))->count();
        return view('admin.stores.index', compact('stores', 'totalInventoryItems'));
    })->name('index');

    Route::get('/create', function () {
        $projects = \App\Models\Project::where('status', 'active')->get();
        return view('admin.stores.create', compact('projects'));
    })->name('create');

    Route::post('/', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code',
            'type' => 'required|in:main,project,warehouse',
            'address' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        \App\Models\Store::create($request->only(['name', 'code', 'type', 'address', 'project_id']));

        return redirect()->route('admin.stores.index')->with('success', 'Store created successfully');
    })->name('store');

    Route::get('/{store}', function (\App\Models\Store $store) {
        $store->load(['project.users', 'inventoryItems']);
        return view('admin.stores.show', compact('store'));
    })->name('show');

    Route::get('/{store}/edit', function (\App\Models\Store $store) {
        $projects = \App\Models\Project::where('status', 'active')->get();
        // Get available managers (stores users not assigned to other stores)
        $availableManagers = \App\Models\User::where('role', 'stores')
            ->where(function($q) use ($store) {
                $q->whereNull('shop_id')->orWhere('shop_id', $store->id);
            })->get();
        return view('admin.stores.edit', compact('store', 'projects', 'availableManagers'));
    })->name('edit');

    Route::put('/{store}', function (\Illuminate\Http\Request $request, \App\Models\Store $store) {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:stores,code,' . $store->id,
            'type' => 'required|in:main,project,warehouse',
            'address' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $store->update($request->only(['name', 'code', 'type', 'address', 'project_id']));

        // Handle manager assignment
        if ($request->has('manager_id')) {
            // Remove old manager assignment
            \App\Models\User::where('shop_id', $store->id)->update(['shop_id' => null]);

            // Assign new manager
            if ($request->manager_id) {
                \App\Models\User::where('id', $request->manager_id)->update(['shop_id' => $store->id]);
            }
        }

        return redirect()->route('admin.stores.index')->with('success', 'Store updated successfully');
    })->name('update');
});

// Inventory Management
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', function () {
        $inventoryItems = \App\Models\InventoryItem::with(['store', 'productCatalog'])
            ->orderBy('store_id')
            ->orderBy('name')
            ->get();
        $stores = \App\Models\Store::all();
        return view('admin.inventory.index', compact('inventoryItems', 'stores'));
    })->name('index');

    Route::post('/add', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $sku = $request->sku ?: 'SKU-' . strtoupper(uniqid());

        \App\Models\InventoryItem::create([
            'store_id' => $request->store_id,
            'name' => $request->name,
            'sku' => $sku,
            'category' => $request->category ?? 'General',
            'unit' => $request->unit,
            'unit_price' => $request->unit_price,
            'quantity' => $request->quantity,
            'reorder_level' => $request->reorder_level ?? 10,
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Item added successfully');
    })->name('add');

    Route::post('/{item}/adjust', function (\Illuminate\Http\Request $request, \App\Models\InventoryItem $item) {
        $request->validate([
            'adjustment_type' => 'required|in:add,reduce,set',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $oldQty = $item->quantity;

        if ($request->adjustment_type === 'add') {
            $item->quantity += $request->quantity;
        } elseif ($request->adjustment_type === 'reduce') {
            $item->quantity = max(0, $item->quantity - $request->quantity);
        } else {
            $item->quantity = $request->quantity;
        }

        $item->save();

        // Log the adjustment
        \App\Models\InventoryLog::create([
            'inventory_item_id' => $item->id,
            'type' => $request->adjustment_type,
            'quantity' => $request->quantity,
            'notes' => $request->notes ?? "Adjusted from {$oldQty} to {$item->quantity}",
            'recorded_by' => auth()->id(),
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Stock adjusted successfully');
    })->name('adjust');

    Route::delete('/{item}', function (\App\Models\InventoryItem $item) {
        $item->delete();
        return redirect()->route('admin.inventory.index')->with('success', 'Item deleted successfully');
    })->name('delete');
});

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            // Projects stats
            $projects = \App\Models\Project::all();

            // Requisitions stats
            $requisitions = \App\Models\Requisition::all();

            // LPOs stats
            $lpos = \App\Models\Lpo::all();

            // Payments stats
            $payments = \App\Models\Payment::all();

            // Inventory stats
            $inventoryItems = \App\Models\InventoryItem::all();

            // Suppliers stats
            $suppliers = \App\Models\Supplier::all();

            $stats = [
                // Projects
                'total_projects' => $projects->count(),
                'active_projects' => $projects->where('status', 'active')->count(),
                'completed_projects' => $projects->where('status', 'completed')->count(),
                'on_hold_projects' => $projects->where('status', 'on_hold')->count(),
                'total_budget' => $projects->sum('budget'),

                // Requisitions
                'total_requisitions' => $requisitions->count(),
                'pending_requisitions' => $requisitions->where('status', 'pending')->count(),
                'approved_requisitions' => $requisitions->whereIn('status', ['operations_approved', 'ceo_approved', 'lpo_issued'])->count(),
                'completed_requisitions' => $requisitions->where('status', 'completed')->count(),
                'rejected_requisitions' => $requisitions->where('status', 'rejected')->count(),
                'requisitions_value' => $requisitions->sum('estimated_total'),

                // LPOs
                'total_lpos' => $lpos->count(),
                'issued_lpos' => $lpos->whereIn('status', ['issued', 'sent', 'pending'])->count(),
                'delivered_lpos' => $lpos->where('status', 'delivered')->count(),
                'cancelled_lpos' => $lpos->where('status', 'cancelled')->count(),
                'lpos_value' => $lpos->sum('total'),

                // Payments
                'total_payments' => $payments->sum('amount'),
                'pending_payments' => $payments->whereIn('approval_status', ['pending_ceo'])->count(),
                'approved_payments' => $payments->where('approval_status', 'ceo_approved')->count(),
                'rejected_payments' => $payments->where('approval_status', 'ceo_rejected')->count(),
                'total_paid' => $payments->where('approval_status', 'ceo_approved')->sum('amount'),

                // Inventory
                'total_stores' => \App\Models\Store::where('type', 'project')->count(),
                'total_inventory_items' => $inventoryItems->count(),
                'low_stock_items' => $inventoryItems->filter(fn($i) => $i->quantity < $i->reorder_level)->count(),
                'inventory_value' => $inventoryItems->sum(fn($i) => $i->quantity * $i->unit_price),

                // Suppliers
                'total_suppliers' => $suppliers->count(),
                'active_suppliers' => $suppliers->where('status', 'active')->count(),
            ];

            $recentRequisitions = \App\Models\Requisition::with('project')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $recentLpos = \App\Models\Lpo::with('supplier')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return view('admin.reports.index', compact('stats', 'recentRequisitions', 'recentLpos'));
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

    // Equipment Management
    Route::prefix('equipments')->name('equipments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminEquipmentController::class, 'index'])->name('index');
        Route::get('/{equipment}', [\App\Http\Controllers\Admin\AdminEquipmentController::class, 'show'])->name('show');
        Route::get('/{equipment}/edit', [\App\Http\Controllers\Admin\AdminEquipmentController::class, 'edit'])->name('edit');
        Route::put('/{equipment}', [\App\Http\Controllers\Admin\AdminEquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [\App\Http\Controllers\Admin\AdminEquipmentController::class, 'destroy'])->name('destroy');
    });
});

// Replace your current API route with this:
Route::get('/api/stores/{store}/inventory', function ($storeId) {
    $store = \App\Models\Store::find($storeId);
    
    if (!$store) {
        return response()->json(['error' => 'Store not found'], 404);
    }
    
    $items = $store->inventoryItems()
        ->with('productCatalog') // Eager load product catalog relationship
        ->where('quantity', '>', 0)
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'product_catalog_id' => $item->product_catalog_id,
                'name' => $item->name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                // Add product catalog name for matching
                'product_name' => $item->productCatalog ? $item->productCatalog->name : $item->name
            ];
        });
    
    \Log::info("Store {$storeId} inventory API response", [
        'store_id' => $storeId,
        'items_count' => $items->count(),
        'items' => $items->toArray()
    ]);
    
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

    // Subcontrollers, Subcontractors and Labor
Route::prefix('subcontractors')->name('subcontractors.')->group(function () {
    Route::get('/', [SubcontractorController::class, 'index'])->name('index');
    Route::get('/create', [SubcontractorController::class, 'create'])->name('create');
    Route::post('/', [SubcontractorController::class, 'store'])->name('store');
    Route::get('/{subcontractor}', [SubcontractorController::class, 'show'])->name('show');
    Route::get('/contract/{projectSubcontractor}/ledger', [SubcontractorController::class, 'ledger'])->name('ledger');
    
    // Payments
    Route::get('/contract/{projectSubcontractor}/payments/create', [SubcontractorPaymentController::class, 'create'])->name('payments.create');
    Route::post('/contract/{projectSubcontractor}/payments', [SubcontractorPaymentController::class, 'store'])->name('payments.store');
});

Route::prefix('labor')->name('labor.')->group(function () {
    Route::get('/', [LaborController::class, 'index'])->name('index');
    Route::get('/create', [LaborController::class, 'create'])->name('create');
    Route::post('/', [LaborController::class, 'store'])->name('store');
     Route::get('/import', [LaborController::class, 'import'])->name('import');
    Route::post('/import', [LaborController::class, 'processImport'])->name('process-import');
    Route::get('/download-template', [LaborController::class, 'downloadTemplate'])->name('download-template');
    Route::get('/{worker}', [LaborController::class, 'show'])->name('show');
    Route::get('/{worker}/payments/create', [LaborController::class, 'processPayment'])->name('payments.create');
    Route::post('/{worker}/payments', [LaborController::class, 'storePayment'])->name('payments.store');
     Route::get('/payments/{payment}/receipt', [LaborController::class, 'generateReceipt'])->name('payments.receipt');

      // NEW: Bulk payment routes
        Route::prefix('bulk-payments')->name('bulk-payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Finance\BulkLaborPaymentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Finance\BulkLaborPaymentController::class, 'create'])->name('create');
            Route::post('/download-template', [\App\Http\Controllers\Finance\BulkLaborPaymentController::class, 'downloadTemplate'])->name('download-template');
            Route::post('/import', [\App\Http\Controllers\Finance\BulkLaborPaymentController::class, 'processBulkImport'])->name('import');
            Route::post('/store', [\App\Http\Controllers\Finance\BulkLaborPaymentController::class, 'storeBulk'])->name('store');
            Route::get('/report', [\App\Http\Controllers\Finance\BulkLaborPaymentController::class, 'getMonthlyReport'])->name('report');
        });
});

    // Equipment Management
    Route::prefix('equipments')->name('equipments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Finance\EquipmentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Finance\EquipmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Finance\EquipmentController::class, 'store'])->name('store');
        Route::get('/{equipment}', [\App\Http\Controllers\Finance\EquipmentController::class, 'show'])->name('show');
        Route::get('/{equipment}/edit', [\App\Http\Controllers\Finance\EquipmentController::class, 'edit'])->name('edit');
        Route::put('/{equipment}', [\App\Http\Controllers\Finance\EquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [\App\Http\Controllers\Finance\EquipmentController::class, 'destroy'])->name('destroy');
    });

    // Office Staff Management
    Route::resource('office-staff', \App\Http\Controllers\Finance\OfficeStaffController::class);
    Route::get('office-staff/{officeStaff}/pay', [\App\Http\Controllers\Finance\OfficeStaffController::class, 'createPayment'])->name('office-staff.create-payment');
    
    // Salary Payments

    // Office Staff Management
    Route::resource('office-staff', \App\Http\Controllers\Finance\OfficeStaffController::class);
    Route::get('office-staff/{officeStaff}/pay', [\App\Http\Controllers\Finance\OfficeStaffController::class, 'createPayment'])->name('office-staff.create-payment');
    
    // Salary Payments
    Route::post('salary-payments', [\App\Http\Controllers\Finance\SalaryPaymentController::class, 'store'])->name('salary-payments.store');
    Route::delete('salary-payments/{payment}', [\App\Http\Controllers\Finance\SalaryPaymentController::class, 'destroy'])->name('salary-payments.destroy');
    
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
        
        // Approval routes
        Route::post('/{requisition}/approve', [CEORequisitionController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [CEORequisitionController::class, 'reject'])->name('reject');
    });
    
    // Move out of Requisitions Group
    Route::resource('office-staff', \App\Http\Controllers\Finance\OfficeStaffController::class);
    Route::get('office-staff/{officeStaff}/pay', [\App\Http\Controllers\Finance\OfficeStaffController::class, 'createPayment'])->name('office-staff.create-payment');
    // Salary Payments
    Route::post('salary-payments', [\App\Http\Controllers\Finance\SalaryPaymentController::class, 'store'])->name('salary-payments.store');
    Route::delete('salary-payments/{payment}', [\App\Http\Controllers\Finance\SalaryPaymentController::class, 'destroy'])->name('salary-payments.destroy');

     // Payment Approval Routes
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/pending', [CEOPaymentController::class, 'pendingPayments'])->name('pending');
        Route::get('/', [CEOPaymentController::class, 'allPayments'])->name('index');
        Route::get('/{payment}', [CEOPaymentController::class, 'showPayment'])->name('show');
        Route::post('/{payment}/approve', [CEOPaymentController::class, 'approvePayment'])->name('approve');
        Route::post('/{payment}/reject', [CEOPaymentController::class, 'rejectPayment'])->name('reject');
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

     Route::prefix('staff-reports')->name('staff-reports.')->group(function () {
        Route::get('/', [CEOStaffReportController::class, 'index'])->name('index');
        Route::get('/{staffReport}', [CEOStaffReportController::class, 'show'])->name('show');
        Route::delete('/{staffReport}', [CEOStaffReportController::class, 'destroy'])->name('destroy');
        Route::get('/{staffReport}/download/{index}', [CEOStaffReportController::class, 'downloadAttachment'])->name('download.attachment');
    });

    // Equipment Overview
    Route::prefix('equipments')->name('equipments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CEO\CEOEquipmentController::class, 'index'])->name('index');
        Route::get('/{equipment}', [\App\Http\Controllers\CEO\CEOEquipmentController::class, 'show'])->name('show');
    });

    // QHSE Reports (Admin/CEO)
    Route::prefix('qhse-reports')->name('qhse-reports.')->group(function () {
        Route::get('/', [CEOQhseReportController::class, 'index'])->name('index');
        Route::get('/{qhseReport}', [CEOQhseReportController::class, 'show'])->name('show');
        Route::delete('/{qhseReport}', [CEOQhseReportController::class, 'destroy'])->name('destroy');
        Route::get('/{qhseReport}/download/{index}', [CEOQhseReportController::class, 'downloadAttachment'])->name('download.attachment');
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
        Route::get('/search-products', [\App\Http\Controllers\ProjectManager\ProjectManagerRequisitionController::class, 'searchProducts'])
            ->name('search-products');
        Route::get('/pending', [ProjectManagerRequisitionController::class, 'pending'])->name('pending'); 
        Route::get('/{requisition}', [ProjectManagerRequisitionController::class, 'show'])->name('show');
        Route::get('/{requisition}/edit', [ProjectManagerRequisitionController::class, 'edit'])->name('edit'); // NEW
    Route::put('/{requisition}', [ProjectManagerRequisitionController::class, 'update'])->name('update'); // NEW
        Route::post('/{requisition}/approve', [ProjectManagerRequisitionController::class, 'approve'])->name('approve');
        Route::post('/{requisition}/reject', [ProjectManagerRequisitionController::class, 'reject'])->name('reject');
        Route::post('/{requisition}/send-to-store', [ProjectManagerRequisitionController::class, 'sendToStore'])->name('send-to-store');
    });
    
    // Projects
     Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectManagerProjectController::class, 'index'])->name('index');
        Route::get('/{project}', [ProjectManagerProjectController::class, 'show'])->name('show');
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
        Route::get('/pending', [EngineerRequisitionController::class, 'pending'])->name('pending');
        Route::get('/search-products', [\App\Http\Controllers\Engineer\EngineerRequisitionController::class, 'searchProducts'])
                ->name('search-products');
                
            Route::get('/{requisition}', [EngineerRequisitionController::class, 'show'])->name('show');
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

// QHSE Reports Operations Routes (protected)
Route::middleware(['auth', 'role:operations'])->prefix('operations/qhse-reports')->name('operations.qhse-reports.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Operations\OperationsQhseReportController::class, 'index'])->name('index');
    Route::get('/{qhseReport}', [\App\Http\Controllers\Operations\OperationsQhseReportController::class, 'show'])->name('show');
    Route::get('/{qhseReport}/download/{index}', [\App\Http\Controllers\Operations\OperationsQhseReportController::class, 'downloadAttachment'])->name('download');
});

// QHSE Reports Public Routes (no authentication required)
Route::prefix('qhse-reports')->name('qhse-reports.')->group(function () {
    Route::get('/submit', [QhseReportController::class, 'create'])->name('create');
    Route::post('/submit', [QhseReportController::class, 'store'])->name('store');
    Route::get('/success', [QhseReportController::class, 'success'])->name('success');
});


// QHSE Reports Admin/CEO Routes (protected) - USING CEO CONTROLLER
Route::middleware(['auth', 'role:admin,ceo'])->prefix('qhse-reports')->name('ceo.qhse-reports.')->group(function () {
    Route::get('/', [CEOQhseReportController::class, 'index'])->name('index');
    Route::get('/{qhseReport}', [CEOQhseReportController::class, 'show'])->name('show');
    Route::delete('/{qhseReport}', [CEOQhseReportController::class, 'destroy'])->name('destroy');
    Route::get('/{qhseReport}/download/{index}', [CEOQhseReportController::class, 'downloadAttachment'])->name('download.attachment');
});