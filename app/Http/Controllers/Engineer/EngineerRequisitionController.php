<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\RequisitionItem;
use App\Models\RequisitionApproval;
use App\Models\User;
use App\Models\Store;
use App\Models\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ProductCategory;

class EngineerRequisitionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $requisitions = Requisition::where('requested_by', $user->id)
            ->with(['project', 'items.productCatalog', 'approvals'])
            ->latest()
            ->paginate(10);

        $projects = $user->projects()->get();

        return view('engineer.requisitions.index', compact('requisitions', 'projects'));
    }

    public function create()
{
    $projects = auth()->user()->projects;
    $projectStores = Store::whereIn('project_id', $projects->pluck('id'))->get();
    
    // Get product catalog with stock information
    $products = ProductCatalog::active()
        ->with(['inventoryItems' => function($query) use ($projects) {
            $query->whereIn('store_id', Store::whereIn('project_id', $projects->pluck('id'))->pluck('id'));
        }])
        ->get();
    
    $categories = ProductCategory::whereHas('products')
        ->orderBy('name')
        ->pluck('name');
    
    return view('engineer.requisitions.create', compact(
        'projects', 
        'projectStores', 
        'products',
        'categories'
    ));
}

    public function store(Request $request)
    {
        // STEP 1: Log everything that comes in
        Log::info('=== ENGINEER REQUISITION STORE METHOD CALLED ===');
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
        Log::info('Items Data:', $request->items);
        
        // STEP 3: Try validation with detailed error catching
        try {
            Log::info('Starting validation...');
            
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
            Log::info('Validated Data:', $validated);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation FAILED!');
            Log::error('Validation Errors:', $e->errors());
            
            // Return with detailed error message
            return back()
                ->withErrors($e->errors())
                ->with('error', 'Validation failed. Check the form for errors.')
                ->withInput();
        }

        // STEP 4: Additional validation for store type
        if ($request->type === 'store') {
            if (!$request->has('store_id') || !$request->store_id) {
                Log::error('Store type selected but no store_id provided');
                return back()->with('error', 'Please select a store for store requisitions.')->withInput();
            }
            
            $request->validate(['store_id' => 'required|exists:stores,id']);
        }

        // STEP 5: Start database transaction
        DB::beginTransaction();
        Log::info('Database transaction started');
        
        try {
            // Generate reference
            $ref = 'REQ-' . strtoupper(Str::random(8));
            Log::info('Generated REF: ' . $ref);

            // Calculate total
            $estimatedTotal = 0;
            foreach ($validated['items'] as $item) {
                $estimatedTotal += $item['quantity'] * $item['unit_price'];
            }
            Log::info('Calculated Total: ' . $estimatedTotal);

            // STEP 6: Create requisition
            Log::info('Creating requisition with data:', [
                'ref' => $ref,
                'project_id' => $validated['project_id'],
                'requested_by' => auth()->id(),
                'type' => $validated['type'],
                'urgency' => $validated['urgency'],
                'status' => 'pending',
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
                'store_id' => $request->store_id,
            ]);

            $requisition = Requisition::create([
                'ref' => $ref,
                'project_id' => $validated['project_id'],
                'requested_by' => auth()->id(),
                'type' => $validated['type'],
                'urgency' => $validated['urgency'],
                'status' => 'pending',
                'estimated_total' => $estimatedTotal,
                'reason' => $validated['reason'],
                'store_id' => $request->store_id,
                'attachments' => $this->handleAttachments($request),
            ]);

            Log::info('Requisition created successfully! ID: ' . $requisition->id);

            // STEP 7: Create items
            foreach ($validated['items'] as $index => $itemData) {
                Log::info("Creating item $index:", $itemData);
                
                $item = $this->createRequisitionItem($requisition, $itemData);
                
                Log::info("Item created! ID: " . $item->id);
            }

            // STEP 8: Create approval record
            $approval = RequisitionApproval::create([
                'requisition_id' => $requisition->id,
                'approved_by' => auth()->id(),
                'role' => 'engineer',
                'action' => 'created',
                'comment' => 'Requisition created and submitted for Project Manager approval',
            ]);
            
            Log::info('Approval record created! ID: ' . $approval->id);

            // STEP 9: Commit transaction
            DB::commit();
            Log::info('=== TRANSACTION COMMITTED SUCCESSFULLY ===');
            Log::info('Requisition REF: ' . $ref);

            return redirect()
                ->route('engineer.requisitions.index')
                ->with('success', "Requisition $ref created successfully! Awaiting Project Manager approval.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('=== EXCEPTION OCCURRED ===');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error File: ' . $e->getFile());
            Log::error('Error Line: ' . $e->getLine());
            Log::error('Stack Trace: ' . $e->getTraceAsString());
            
            return back()
                ->with('error', 'Failed to create requisition: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function createRequisitionItem($requisition, $itemData)
    {
        $productCatalogId = $itemData['product_catalog_id'] ?? null;
        
        // If no catalog ID, try to find/create one
        if (!$productCatalogId) {
            $product = ProductCatalog::where('name', 'LIKE', $itemData['name'])->first();
            
            if (!$product) {
                Log::info('Creating new product catalog entry', [
                    'name' => $itemData['name'],
                    'unit' => $itemData['unit']
                ]);
                
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
        // Get total available stock across all project stores
        $totalStock = $product->inventoryItems
            ->where('quantity', '>', 0)
            ->sum('quantity');
            
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
        if ($requisition->requested_by !== auth()->id()) {
            abort(403, 'Unauthorized access to this requisition.');
        }

        $requisition->load([
            'project',
            'items.productCatalog', 
            'approvals.approver',
            'lpo.items',
            'lpo.receivedItems.lpoItem'
        ]);

        return view('engineer.requisitions.show', compact('requisition'));
    }

    public function pending()
    {
        $user = auth()->user();
        
        $pendingRequisitions = Requisition::where('requested_by', $user->id)
            ->where('status', 'pending')
            ->with(['project', 'items.productCatalog'])
            ->latest()
            ->paginate(10);

        return view('engineer.requisitions.pending', compact('pendingRequisitions'));
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
}