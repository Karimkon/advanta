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
        $pendingRequisitions = Requisition::where('store_id', $store->id)
            ->where('type', Requisition::TYPE_STORE)
            ->where('status', Requisition::STATUS_PROJECT_MANAGER_APPROVED)
            ->with(['project', 'requester', 'items'])
            ->latest()
            ->paginate(20);

        $releases = StoreRelease::where('store_id', $store->id)
            ->with(['requisition', 'requisition.project'])
            ->latest()
            ->paginate(20);

        return view('stores.releases.index', compact('store', 'pendingRequisitions', 'releases'));
    }

    public function create(Store $store, Requisition $requisition)
    {
        // Verify requisition belongs to this store and is ready for release
        if ($requisition->store_id !== $store->id || 
            $requisition->status !== Requisition::STATUS_PROJECT_MANAGER_APPROVED ||
            $requisition->type !== Requisition::TYPE_STORE) {
            abort(403, 'Invalid requisition for store release.');
        }

        $requisition->load(['items']);
        
        // Check stock availability
        foreach ($requisition->items as $item) {
            $inventoryItem = InventoryItem::where('store_id', $store->id)
                ->where('name', $item->description)
                ->first();
            
            $item->available_stock = $inventoryItem ? $inventoryItem->quantity : 0;
            $item->can_fulfill = $inventoryItem && $inventoryItem->quantity >= $item->quantity;
        }

        return view('stores.releases.create', compact('store', 'requisition'));
    }

    public function store(Request $request, Store $store, Requisition $requisition)
    {
        // Validation
        $request->validate([
            'items' => 'required|array',
            'items.*.quantity_released' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Create store release
            $storeRelease = StoreRelease::create([
                'requisition_id' => $requisition->id,
                'store_id' => $store->id,
                'project_id' => $requisition->project_id,
                'released_by' => auth()->id(),
                'released_at' => now(),
                'status' => StoreRelease::STATUS_RELEASED,
                'notes' => $request->notes,
            ]);

            $totalReleased = 0;

            // Process each item
            foreach ($request->items as $itemId => $itemData) {
                $requisitionItem = $requisition->items()->findOrFail($itemId);
                $quantityReleased = $itemData['quantity_released'];

                if ($quantityReleased > 0) {
                    // Find inventory item
                    $inventoryItem = InventoryItem::where('store_id', $store->id)
                        ->where('name', $requisitionItem->description)
                        ->first();

                    if ($inventoryItem && $inventoryItem->quantity >= $quantityReleased) {
                        // Create release item record
                        StoreReleaseItem::create([
                            'store_release_id' => $storeRelease->id,
                            'inventory_item_id' => $inventoryItem->id,
                            'requisition_item_id' => $requisitionItem->id,
                            'quantity_requested' => $requisitionItem->quantity,
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
                }
            }

            // Update requisition status if all items are released
            if ($totalReleased > 0) {
                $requisition->update([
                    'status' => Requisition::STATUS_COMPLETED
                ]);
            }

            DB::commit();

            return redirect()->route('stores.releases.index', $store)
                ->with('success', 'Store release completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process store release: ' . $e->getMessage());
        }
    }

    public function show(Store $store, StoreRelease $release)
    {
        if ($release->store_id !== $store->id) {
            abort(403, 'Unauthorized access.');
        }

        $release->load(['items', 'items.inventoryItem', 'items.requisitionItem', 'requisition.project']);

        return view('stores.releases.show', compact('store', 'release'));
    }
}