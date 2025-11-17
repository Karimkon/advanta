<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\InventoryLog;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Store $store)
    {
        $user = auth()->user();
        $stores = Store::all();
        
        // Authorization check
        if (!$this->canAccessStore($user, $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        // Get stock movements with filters - FIXED: using correct relationships
        $movements = InventoryLog::whereHas('item', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->with(['item', 'user', 'project'])
            ->latest()
            ->paginate(50);

        // Summary statistics - FIXED: using correct relationships
        $summary = [
            'total_movements' => $movements->total(),
            'stock_ins' => InventoryLog::whereHas('item', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })->where('type', 'in')->count(),
            'stock_outs' => InventoryLog::whereHas('item', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })->where('type', 'out')->count(),
            'adjustments' => InventoryLog::whereHas('item', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })->where('type', 'adjustment')->count(),
            'total_value_moved' => InventoryLog::whereHas('item', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })->sum(DB::raw('quantity * unit_price')),
        ];

        // Get items for filter dropdown
        $items = $store->inventoryItems()->orderBy('name')->get();

        return view('stores.movements.index', compact('store', 'movements', 'summary', 'items', 'stores'));
    }

    public function filter(Request $request, Store $store)
    {
        $user = auth()->user();
        
        if (!$this->canAccessStore($user, $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $query = InventoryLog::whereHas('item', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->with(['item', 'user', 'project']);

        // Apply filters
        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->item_id) {
            $query->where('inventory_item_id', $request->item_id);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(50);

        return view('stores.movements.partials.movements_table', compact('movements'));
    }

    public function export(Store $store, Request $request)
    {
        $user = auth()->user();
        
        if (!$this->canAccessStore($user, $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $movements = InventoryLog::whereHas('item', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->with(['item', 'user', 'project'])->get();

        $filename = "stock_movements_{$store->name}_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Date', 'Time', 'Item Name', 'SKU', 'Type', 'Quantity', 
                'Unit Price', 'Total Value', 'Balance After', 'User', 'Project', 'Notes'
            ]);

            // Data rows
            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->created_at->format('Y-m-d'),
                    $movement->created_at->format('H:i:s'),
                    $movement->item->name,
                    $movement->item->sku,
                    strtoupper($movement->type),
                    $movement->quantity,
                    $movement->unit_price,
                    $movement->quantity * $movement->unit_price,
                    $movement->balance_after,
                    $movement->user->name ?? 'System',
                    $movement->project->name ?? 'N/A',
                    $movement->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function canAccessStore($user, $store)
    {
        if ($user->id === 6 && $store->isMainStore()) {
            return true;
        }

        if ($store->isProjectStore() && $store->project) {
            return $store->project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}