<?php

namespace App\Services;

use App\Models\Lpo;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Store;
use App\Models\Project;
use App\Models\LpoReceivedItem; 
use Illuminate\Support\Facades\DB;

class StoreService
{
     public function processDeliveredLpo(Lpo $lpo, $receivedItems = null)
    {
        DB::beginTransaction();
        try {
            $requisition = $lpo->requisition;
            $project = $requisition->project;
            
            // Get the project's store
            $store = Store::where('project_id', $project->id)->first();
            
            if (!$store) {
                throw new \Exception("No store found for project: {$project->name}");
            }

            foreach ($lpo->items as $lpoItem) {
                // Get the received quantity (default to full quantity if not specified)
                $receivedQty = $receivedItems[$lpoItem->id]['quantity_received'] ?? $lpoItem->quantity;
                $condition = $receivedItems[$lpoItem->id]['condition'] ?? 'good';
                
                if ($receivedQty > 0) {
                    $this->addItemToStore($lpoItem, $store, $project, $lpo, $receivedQty, $condition);
                    
                    // Record what was actually received
                    LpoReceivedItem::create([
                        'lpo_id' => $lpo->id,
                        'lpo_item_id' => $lpoItem->id,
                        'quantity_ordered' => $lpoItem->quantity,
                        'quantity_received' => $receivedQty,
                        'condition' => $condition,
                        'received_by' => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function addItemToStore($lpoItem, $store, $project, $lpo, $receivedQty, $condition)
{
    // Try to find by product catalog first
    $inventoryItem = null;
    
    if ($lpoItem->product_catalog_id) {
        $inventoryItem = InventoryItem::where('store_id', $store->id)
            ->where('product_catalog_id', $lpoItem->product_catalog_id)
            ->first();
    }
    
    // If not found by product catalog, try by name (backward compatibility)
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
        // Create new inventory item with product catalog link
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
        'user_id' => auth()->id(),
        'type' => 'in',
        'quantity' => $receivedQty,
        'unit_price' => $lpoItem->unit_price,
        'balance_after' => $newQuantity,
        'notes' => "LPO Delivery: {$lpo->lpo_number} - {$lpoItem->description}" . 
                  ($condition !== 'good' ? " (Condition: {$condition})" : ""),
    ]);
}

    public function getProjectStoreInventory($projectId)
    {
        $store = Store::where('project_id', $projectId)->first();
        
        if (!$store) {
            return collect();
        }

        return $store->inventoryItems()
            ->with(['logs' => function($query) {
                $query->latest()->take(5);
            }])
            ->latest()
            ->get();
    }

    public function getStoreStats($storeId)
    {
        $store = Store::findOrFail($storeId);
        
        return [
            'total_items' => $store->getTotalItemsCount(),
            'total_quantity' => $store->getTotalQuantity(),
            'store_value' => $store->getStoreValue(),
            'low_stock_items' => $store->getLowStockItems()->count(),
            'out_of_stock_items' => $store->getOutOfStockItems()->count(),
        ];
    }
}