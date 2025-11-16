<?php

namespace App\Services;

use App\Models\Lpo;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use App\Models\Store;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function processDeliveredLpo(Lpo $lpo)
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
                $this->addItemToStore($lpoItem, $store, $project, $lpo);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function addItemToStore($lpoItem, $store, $project, $lpo)
    {
        // Find existing inventory item or create new one
        $inventoryItem = InventoryItem::where('store_id', $store->id)
            ->where('name', $lpoItem->description)
            ->first();

        if ($inventoryItem) {
            // Update existing item
            $oldQuantity = $inventoryItem->quantity;
            $newQuantity = $oldQuantity + $lpoItem->quantity;
            
            $inventoryItem->update([
                'quantity' => $newQuantity,
                'unit_price' => $lpoItem->unit_price, // Update to latest price
            ]);
        } else {
            // Create new inventory item
            $inventoryItem = InventoryItem::create([
                'name' => $lpoItem->description,
                'description' => $lpoItem->description,
                'sku' => 'SKU-' . strtoupper(uniqid()),
                'category' => 'General',
                'unit_price' => $lpoItem->unit_price,
                'unit' => $lpoItem->unit,
                'quantity' => $lpoItem->quantity,
                'reorder_level' => 10, // Default reorder level
                'track_per_project' => true,
                'store_id' => $store->id,
                'project_id' => $project->id,
            ]);
            $oldQuantity = 0;
            $newQuantity = $lpoItem->quantity;
        }

        // Create inventory log
        InventoryLog::create([
            'inventory_item_id' => $inventoryItem->id,
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'type' => 'in',
            'quantity' => $lpoItem->quantity,
            'unit_price' => $lpoItem->unit_price,
            'balance_after' => $newQuantity,
            'notes' => "LPO Delivery: {$lpo->lpo_number} - {$lpoItem->description}",
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