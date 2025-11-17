<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreInventoryController extends Controller
{
    public function index(Store $store)
    {
        $stores = Store::all();
        $user = auth()->user();
        
        // Authorization - check if user has access to this store
        if (!$this->canAccessStore($user, $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        // FIX: Use paginate() instead of get() to avoid Collection error
        $inventoryItems = $store->inventoryItems()
            ->with(['logs' => function($query) {
                $query->latest()->take(5);
            }])
            ->latest()
            ->paginate(20); // Changed from get() to paginate(20)

        $stats = [
            'total_items' => $store->inventoryItems()->count(),
            'total_quantity' => $store->inventoryItems()->sum('quantity'),
            'store_value' => $store->inventoryItems()->sum(DB::raw('quantity * unit_price')),
            'low_stock_items' => $store->inventoryItems()->where('quantity', '<', DB::raw('reorder_level'))->count(),
            'out_of_stock_items' => $store->inventoryItems()->where('quantity', '<=', 0)->count(),
        ];

        return view('stores.inventory.index', compact('store', 'inventoryItems', 'stats', 'stores'));
    }

    private function canAccessStore($user, $store)
    {
        // Main store manager (ID 6) can access main store
        if ($user->id === 6 && $store->isMainStore()) {
            return true;
        }

        // Project store users can access their project stores
        if ($store->isProjectStore() && $store->project) {
            return $store->project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function show(Store $store, InventoryItem $inventoryItem)
    {
        // Verify the inventory item belongs to the store
        if ($inventoryItem->store_id !== $store->id) {
            abort(403, 'This inventory item does not belong to the selected store.');
        }

        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $inventoryItem->load(['logs.user']);

        return view('stores.inventory.show', compact('store', 'inventoryItem'));
    }

    public function create(Store $store)
    {
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        return view('stores.inventory.create', compact('store'));
    }

    public function store(Request $request, Store $store)
    {
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create inventory item
            $inventoryItem = InventoryItem::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'unit' => $request->unit,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'reorder_level' => $request->reorder_level,
                'store_id' => $store->id,
                'project_id' => $store->project_id,
                'sku' => 'SKU-' . strtoupper(uniqid()),
                'track_per_project' => true,
            ]);

            // Create initial inventory log
            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'project_id' => $store->project_id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'balance_after' => $request->quantity,
                'notes' => 'Initial stock entry',
            ]);

            DB::commit();

            return redirect()->route('stores.inventory.index', $store)
                ->with('success', 'Inventory item added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add inventory item: ' . $e->getMessage());
        }
    }

    public function adjustStock(Store $store, InventoryItem $inventoryItem, Request $request)
    {
        // Verify the inventory item belongs to the store
        if ($inventoryItem->store_id !== $store->id) {
            abort(403, 'Unauthorized access.');
        }

        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $request->validate([
            'adjustment_type' => 'required|in:add,remove,set',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $oldQuantity = $inventoryItem->quantity;
            
            switch ($request->adjustment_type) {
                case 'add':
                    $newQuantity = $oldQuantity + $request->quantity;
                    $logType = 'in';
                    break;
                case 'remove':
                    if ($request->quantity > $oldQuantity) {
                        return back()->with('error', 'Cannot remove more than available stock.');
                    }
                    $newQuantity = max(0, $oldQuantity - $request->quantity);
                    $logType = 'out';
                    break;
                case 'set':
                    $newQuantity = $request->quantity;
                    $logType = 'adjustment';
                    break;
                default:
                    return back()->with('error', 'Invalid adjustment type.');
            }

            // Update inventory item
            $inventoryItem->update([
                'quantity' => $newQuantity,
            ]);

            // Create inventory log
            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'project_id' => $store->project_id,
                'user_id' => auth()->id(),
                'type' => $logType,
                'quantity' => $request->quantity,
                'unit_price' => $inventoryItem->unit_price,
                'balance_after' => $newQuantity,
                'notes' => $request->notes ?? "Manual stock adjustment",
            ]);

            DB::commit();

            return redirect()->route('stores.inventory.show', [$store, $inventoryItem])
                ->with('success', 'Stock adjusted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }
}