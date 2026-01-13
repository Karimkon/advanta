<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Requisition;
use App\Models\StoreRelease;
use App\Models\StoreReleaseItem;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreReleaseController extends Controller
{
    public function index(Store $store)
    {
        $user = auth()->user();
        $stores = Store::all();
        
        // Authorization check
        if (!$this->canAccessStore($user, $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        // Get requisitions ready for release (approved and for this store)
        $pendingRequisitions = Requisition::where('store_id', $store->id)
            ->where('type', Requisition::TYPE_STORE)
            ->whereIn('status', [
                Requisition::STATUS_PROJECT_MANAGER_APPROVED
            ])
            ->with(['project', 'requester', 'items'])
            ->latest()
            ->paginate(20);

        $releases = StoreRelease::where('store_id', $store->id)
            ->with(['requisition', 'requisition.project', 'releasedBy'])
            ->latest()
            ->paginate(20);

        return view('stores.releases.index', compact('store', 'pendingRequisitions', 'releases', 'stores'));
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

public function create(Store $store, Requisition $requisition)
{
    $stores = Store::all();

    // Authorization check
    if (!$this->canAccessStore(auth()->user(), $store)) {
        abort(403, 'Unauthorized access to this store.');
    }

    if ($requisition->store_id !== $store->id || 
        $requisition->status !== Requisition::STATUS_PROJECT_MANAGER_APPROVED ||
        $requisition->type !== Requisition::TYPE_STORE) {
        abort(403, 'Invalid requisition for store release.');
    }

    $requisition->load(['items']);
    
    // Enhanced inventory item matching
    foreach ($requisition->items as $item) {
        $inventoryItem = null;
        
        // 1. Try by product catalog ID first (most reliable)
        if ($item->product_catalog_id) {
            $inventoryItem = InventoryItem::where('store_id', $store->id)
                ->where('product_catalog_id', $item->product_catalog_id)
                ->first();
        }
        
        // 2. Try by exact name match
        if (!$inventoryItem) {
            $inventoryItem = InventoryItem::where('store_id', $store->id)
                ->where('name', $item->name)
                ->first();
        }
        
        // 3. Try by partial name match (item name contained in inventory name)
        if (!$inventoryItem) {
            $inventoryItem = InventoryItem::where('store_id', $store->id)
                ->where('name', 'like', '%' . $item->name . '%')
                ->first();
        }
        
        // 4. Try reverse - inventory name contained in item name
        if (!$inventoryItem) {
            $inventoryItem = InventoryItem::where('store_id', $store->id)
                ->whereRaw('? LIKE CONCAT("%", name, "%")', [$item->name])
                ->first();
        }
        
        // 5. Try by SKU if the item name looks like it contains a SKU
        if (!$inventoryItem && preg_match('/SKU-[A-Z0-9]+/i', $item->name, $matches)) {
            $inventoryItem = InventoryItem::where('store_id', $store->id)
                ->where('sku', $matches[0])
                ->first();
        }
        
        // 6. Try fuzzy match - extract key words and match
        if (!$inventoryItem) {
            // Extract first few significant words from item name
            $words = preg_split('/[\s\-\_\(\)]+/', $item->name);
            $significantWords = array_filter($words, fn($w) => strlen($w) > 3);
            $firstWords = array_slice($significantWords, 0, 3);
            
            if (count($firstWords) >= 2) {
                $searchPattern = '%' . implode('%', $firstWords) . '%';
                $inventoryItem = InventoryItem::where('store_id', $store->id)
                    ->where('name', 'like', $searchPattern)
                    ->first();
            }
        }
        
        // Log for debugging
        \Log::info("Store Release Matching", [
            'requisition_item_id' => $item->id,
            'requisition_item_name' => $item->name,
            'product_catalog_id' => $item->product_catalog_id,
            'store_id' => $store->id,
            'matched_inventory_item' => $inventoryItem ? [
                'id' => $inventoryItem->id,
                'name' => $inventoryItem->name,
                'quantity' => $inventoryItem->quantity
            ] : null
        ]);
        
        $item->available_stock = $inventoryItem ? $inventoryItem->quantity : 0;
        $item->can_fulfill = $inventoryItem && $inventoryItem->quantity >= $item->quantity;
        $item->inventory_item = $inventoryItem;
    }

    return view('stores.releases.create', compact('store', 'requisition', 'stores'));
}

    public function store(Request $request, Store $store, Requisition $requisition)
{
    // Authorization check
    if (!$this->canAccessStore(auth()->user(), $store)) {
        abort(403, 'Unauthorized access to this store.');
    }

    // Validation
    $request->validate([
        'items' => 'required|array',
        'items.*.quantity_released' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        // Create store release - FIX: Set released_at field
        $storeRelease = StoreRelease::create([
            'requisition_id' => $requisition->id,
            'store_id' => $store->id,
            'released_by' => auth()->id(),
            'released_at' => now(), // ADD THIS LINE
            'status' => StoreRelease::STATUS_RELEASED,
            'notes' => $request->notes,
        ]);

        $totalReleased = 0;
        $allItemsReleased = true;

        // Process each item
        foreach ($request->items as $index => $itemData) {
            $quantityReleased = floatval($itemData['quantity_released']);
            
            if ($quantityReleased <= 0) {
                $allItemsReleased = false;
                continue;
            }

            $requisitionItemId = $itemData['requisition_item_id'];
            $inventoryItemId = $itemData['inventory_item_id'];
            
            $requisitionItem = $requisition->items()->findOrFail($requisitionItemId);
            $inventoryItem = InventoryItem::find($inventoryItemId);

            if (!$inventoryItem) {
                throw new \Exception("Inventory item not found for ID: {$inventoryItemId}");
            }

            // Check if sufficient stock exists
            if ($inventoryItem->quantity < $quantityReleased) {
                throw new \Exception("Insufficient stock for {$inventoryItem->name}. Available: {$inventoryItem->quantity}, Requested: {$quantityReleased}");
            }

            // Create release item record
            StoreReleaseItem::create([
                'store_release_id' => $storeRelease->id,
                'inventory_item_id' => $inventoryItem->id,
                'requisition_item_id' => $requisitionItem->id,
                'quantity_requested' => $itemData['quantity_requested'],
                'quantity_released' => $quantityReleased,
                'notes' => $itemData['notes'] ?? null,
            ]);

            // Update inventory
            $oldQuantity = $inventoryItem->quantity;
            $newQuantity = $oldQuantity - $quantityReleased;
            
            $inventoryItem->update(['quantity' => $newQuantity]);

            // Create inventory log
            InventoryLog::create([
                'inventory_item_id' => $inventoryItem->id,
                'project_id' => $store->project_id,
                'user_id' => auth()->id(),
                'type' => 'out',
                'quantity' => $quantityReleased,
                'unit_price' => $inventoryItem->unit_price,
                'balance_after' => $newQuantity,
                'notes' => "Store release for requisition: {$requisition->ref}",
            ]);

            $totalReleased += $quantityReleased;
        }

        // Update requisition status
        if ($totalReleased > 0) {
            $newStatus = $allItemsReleased ? Requisition::STATUS_COMPLETED : Requisition::STATUS_PARTIAL;
            $requisition->update(['status' => $newStatus]);
        } else {
            throw new \Exception("No items were released. Please specify quantities to release.");
        }

        DB::commit();

        return redirect()->route('stores.releases.show', [$store, $storeRelease])
            ->with('success', 'Store release completed successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to process store release: ' . $e->getMessage())->withInput();
    }
}

    public function show(Store $store, StoreRelease $release)
    {
        $stores = Store::all();
        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        if ($release->store_id !== $store->id) {
            abort(403, 'Unauthorized access.');
        }

        $release->load(['items', 'items.inventoryItem', 'items.requisitionItem', 'requisition.project']);

        return view('stores.releases.show', compact('store', 'release', 'stores'));
    }
}