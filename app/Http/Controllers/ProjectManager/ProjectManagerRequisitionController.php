<?php

namespace App\Http\Controllers\ProjectManager;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\RequisitionItem;
use App\Models\RequisitionApproval;
use App\Models\User;
use App\Models\Store;
use App\Models\ProductCatalog;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProjectManagerRequisitionController extends Controller
{
   public function index()
{
    $user = auth()->user();
    
    // Get ALL requisitions from projects managed by this PM (not just ones they created)
    $query = Requisition::whereHas('project.users', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })
    ->with(['project', 'items', 'approvals', 'requester']);

    // Apply filters
    if (request('status')) {
        $query->where('status', request('status'));
    }
    
    if (request('type')) {
        $query->where('type', request('type'));
    }
    
    if (request('project_id')) {
        $query->where('project_id', request('project_id'));
    }

    $requisitions = $query->latest()->paginate(10);

    // Get projects for filter dropdown
    $projects = Project::whereHas('users', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get();  

    return view('project_manager.requisitions.index', compact('requisitions', 'projects'));
}

   public function create()
{
    $user = auth()->user();
    
    // Get projects managed by this project manager
    $projects = Project::whereHas('users', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get();

    // Get project stores
    $projectStores = Store::whereHas('project.users', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get();

    // Get product catalog with stock information
    $products = ProductCatalog::active()
        ->with(['inventoryItems' => function($query) use ($projects) {
            $query->whereIn('store_id', Store::whereIn('project_id', $projects->pluck('id'))->pluck('id'));
        }])
        ->get();
    
    $categories = ProductCategory::whereHas('products')
        ->orderBy('name')
        ->pluck('name');

    return view('project_manager.requisitions.create', compact(
        'projects', 
        'projectStores', 
        'products',
        'categories'
    ));
}

    public function store(Request $request)
    {
        // STEP 1: Log everything that comes in
        Log::info('=== PROJECT MANAGER REQUISITION STORE METHOD CALLED ===');
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('User: ' . auth()->user()->email);
        Log::info('All Request Data:', $request->all());
        
        // STEP 2: Check if items exist
        if (!$request->has('items')) {
            Log::error('NO ITEMS IN REQUEST!');
            return back()->with('error', 'No items provided. Please add at least one product.')->withInput();
        }
        
        Log::info('Items Count: ' . count($request->items));
        
        // STEP 3: Try validation
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'type' => 'required|in:store,purchase',
                'urgency' => 'required|in:low,medium,high',
                'reason' => 'required|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_catalog_id' => 'nullable|exists:product_catalogs,id',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit' => 'required|string|max:50',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.notes' => 'nullable|string|max:500',
                'items.*.from_store' => 'nullable|boolean',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240',
            ]);
            
            Log::info('Validation PASSED!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation FAILED!', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        }

        // STEP 4: Additional validation for store type
        if ($request->type === 'store') {
            $request->validate(['store_id' => 'required|exists:stores,id']);
        }

        // STEP 5: Start database transaction
        DB::beginTransaction();
        
        try {
            // Generate reference
            $ref = 'REQ-' . strtoupper(Str::random(8));

            // Calculate total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }

            // For purchase requisitions created by PM, auto-approve
            $status = $validated['type'] === 'purchase' ? 'project_manager_approved' : 'pending';

            // STEP 6: Create requisition
            $requisition = Requisition::create([
                'ref' => $ref,
                'project_id' => $validated['project_id'],
                'requested_by' => auth()->id(),
                'type' => $validated['type'],
                'urgency' => $validated['urgency'],
                'status' => $status,
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
                'store_id' => $request->store_id,
                'attachments' => $this->handleAttachments($request),
            ]);

            // STEP 7: Create items
            foreach ($validated['items'] as $itemData) {
                $this->createRequisitionItem($requisition, $itemData);
            }

            // STEP 8: Create approval record
            $action = $validated['type'] === 'purchase' ? 'auto_approved' : 'created';
            $comment = $validated['type'] === 'purchase' 
                ? 'Purchase requisition created and auto-approved by Project Manager' 
                : 'Requisition created and submitted for approval';

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => $action,
                'comment' => $comment,
            ]);

            DB::commit();

            $message = $validated['type'] === 'purchase' 
                ? "Purchase requisition $ref created and sent to Operations!" 
                : "Store requisition $ref created successfully!";

            return redirect()->route('project_manager.requisitions.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating PM requisition: ' . $e->getMessage());
            return back()->with('error', 'Failed to create requisition: ' . $e->getMessage())->withInput();
        }
    }

    private function createRequisitionItem($requisition, $itemData)
    {
        $productCatalogId = $itemData['product_catalog_id'] ?? null;
        
        if (!$productCatalogId) {
            $product = ProductCatalog::where('name', 'LIKE', $itemData['name'])->first();
            if (!$product) {
                $product = ProductCatalog::create([
                    'name' => $itemData['name'],
                    'unit' => $itemData['unit'],
                    'category' => 'Custom Items',
                    'description' => 'Auto-created from requisition',
                    'is_active' => true,
                ]);
            }
            $productCatalogId = $product->id;
        }
        
        return RequisitionItem::create([
            'requisition_id' => $requisition->id,
            'product_catalog_id' => $productCatalogId,
            'name' => $itemData['name'],
            'quantity' => $itemData['quantity'],
            'unit' => $itemData['unit'],
            'unit_price' => $itemData['unit_price'],
            'total_price' => $itemData['quantity'] * $itemData['unit_price'],
            'notes' => $itemData['notes'] ?? null,
            'from_store' => $itemData['from_store'] ?? false,
        ]);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->q;
        $category = $request->category;
        
        $query = ProductCatalog::active();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        if ($category) {
            $query->where('category', $category);
        }
        
        $products = $query->limit(50)->get();
        
        $results = $products->map(function($product) {
            $totalStock = $product->inventoryItems->where('quantity', '>', 0)->sum('quantity');
                
            return [
                'id' => $product->id,
                'text' => $product->name . ($product->sku ? " ({$product->sku})" : ''),
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'unit' => $product->unit,
                'available_stock' => $totalStock,
                'has_stock' => $totalStock > 0
            ];
        });
        
        return response()->json(['results' => $results]);
    }

    

    public function show(Requisition $requisition)
    {
        // Authorization - ensure project manager owns this requisition or manages the project
        $this->authorizeRequisitionAccess($requisition);

        $requisition->load([
            'project', 
            'requester', 
            'items', 
            'approvals.approver',
            'lpo.supplier',
            'store'
        ]);

        return view('project_manager.requisitions.show', compact('requisition'));
    }

    public function pending()
{
    $user = auth()->user();
    
    $pendingRequisitions = Requisition::whereHas('project', function($query) use ($user) {
        $query->whereHas('users', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    })
    ->where('status', Requisition::STATUS_PENDING)
    // Removed: ->where('type', Requisition::TYPE_STORE)
    ->with(['project', 'requester', 'items'])
    ->latest()
    ->paginate(20);

    return view('project_manager.requisitions.pending', compact('pendingRequisitions'));
}

    public function approve(Requisition $requisition)
    {
        // Authorization - ensure project manager can approve this requisition
        $this->authorizeRequisitionApproval($requisition);

        DB::beginTransaction();
        try {
            $nextStatus = $requisition->isStoreRequisition() 
                ? 'project_manager_approved' 
                : 'project_manager_approved';

            $requisition->update(['status' => $nextStatus]);

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => 'approved',
                'comment' => request('comment', 'Approved by Project Manager'),
                'approved_amount' => $requisition->estimated_total,
            ]);

            DB::commit();

            return back()->with('success', 'Requisition approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve requisition: ' . $e->getMessage());
        }
    }

    public function reject(Requisition $requisition)
    {
        // Authorization - ensure project manager can approve this requisition
        $this->authorizeRequisitionApproval($requisition);

        $validated = request()->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $requisition->update(['status' => 'rejected']);

            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => 'rejected',
                'comment' => $validated['rejection_reason'],
            ]);

            DB::commit();

            return back()->with('success', 'Requisition rejected successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject requisition: ' . $e->getMessage());
        }
    }

    private function authorizeRequisitionAccess(Requisition $requisition)
    {
        $user = auth()->user();
        
        // Check if user owns the requisition or manages the project
        if ($requisition->requested_by !== $user->id && 
            !$requisition->project->users->contains($user->id)) {
            abort(403, 'Unauthorized access to this requisition.');
        }
    }

    private function authorizeRequisitionApproval(Requisition $requisition)
    {
        $user = auth()->user();
        
        // Check if user manages the project and requisition is pending
        if (!$requisition->project->users->contains($user->id) || 
            $requisition->status !== 'pending') {
            abort(403, 'Unauthorized to approve this requisition.');
        }
    }

    private function handleAttachments(Request $request)
    {
        if (!$request->hasFile('attachments')) {
            return null;
        }

        $attachments = [];
        foreach ($request->file('attachments') as $file) {
            if ($file->isValid()) {
                $path = $file->store('requisitions/attachments', 'public');
                $attachments[] = $path;
            }
        }

        return json_encode($attachments);
    }

    public function edit(Requisition $requisition)
    {
        // Authorization - ensure project manager owns this requisition
        $this->authorizeRequisitionAccess($requisition);

        // Only allow editing of pending requisitions
        if (!$requisition->canBeEdited()) {
            return redirect()->route('project_manager.requisitions.show', $requisition)
                ->with('error', 'Only pending requisitions can be edited.');
        }

        $user = auth()->user();
        
        // Get projects managed by this project manager
        $projects = Project::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Get project stores
        $projectStores = Store::whereHas('project.users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        // Get product catalog with stock information
        $products = ProductCatalog::active()
            ->with(['inventoryItems' => function($query) use ($projects) {
                $query->whereIn('store_id', Store::whereIn('project_id', $projects->pluck('id'))->pluck('id'));
            }])
            ->get();
        
        $categories = ProductCategory::whereHas('products')
            ->orderBy('name')
            ->pluck('name');

        $requisition->load('items.productCatalog');

        return view('project_manager.requisitions.edit', compact(
            'requisition', 
            'projects', 
            'projectStores', 
            'products', 
            'categories'
        ));
    }

    public function update(Request $request, Requisition $requisition)
    {
        Log::info('=== PROJECT MANAGER REQUISITION UPDATE METHOD CALLED ===');
        Log::info('Requisition ID: ' . $requisition->id);
        Log::info('User: ' . auth()->user()->email);
        
        // Authorization
        $this->authorizeRequisitionAccess($requisition);

        if (!$requisition->canBeEdited()) {
            return back()->with('error', 'This requisition cannot be edited in its current status.');
        }

        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'urgency' => 'required|in:low,medium,high',
                'reason' => 'required|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_catalog_id' => 'nullable|exists:product_catalogs,id',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit' => 'required|string|max:50',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.notes' => 'nullable|string|max:500',
                'items.*.from_store' => 'nullable|boolean',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240',
            ]);
            
            Log::info('Update Validation PASSED!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Update Validation FAILED!', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Calculate total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }

            // Handle attachments if new ones are uploaded
            $newAttachments = $this->handleAttachments($request);
            $attachments = $requisition->attachments;
            
            if ($newAttachments) {
                $existing = json_decode($attachments, true) ?? [];
                $added = json_decode($newAttachments, true) ?? [];
                $attachments = json_encode(array_merge($existing, $added));
            }

            // Update requisition
            $requisition->update([
                'project_id' => $validated['project_id'],
                'urgency' => $validated['urgency'],
                'reason' => $validated['reason'],
                'estimated_total' => $estimatedTotal,
                'attachments' => $attachments,
            ]);

            // Sync items: Delete old items and create new ones
            $requisition->items()->delete();
            
            foreach ($validated['items'] as $itemData) {
                $this->createRequisitionItem($requisition, $itemData);
            }

            // Record update action
            RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'project_manager',
                'action' => 'updated',
                'comment' => 'Requisition details and items updated',
            ]);

            DB::commit();

            return redirect()->route('project_manager.requisitions.show', $requisition)
                ->with('success', 'Requisition updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating PM requisition: ' . $e->getMessage());
            return back()->with('error', 'Failed to update requisition: ' . $e->getMessage())->withInput();
        }
    }
}