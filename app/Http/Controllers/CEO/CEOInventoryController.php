<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CEOInventoryController extends Controller
{
    public function index()
    {
        // Get all stores with their inventory summary
        $stores = Store::with(['project', 'inventoryItems'])
            ->get()
            ->map(function($store) {
                $inventoryItems = $store->inventoryItems;
                
                $totalItems = $inventoryItems->count();
                $totalQuantity = $inventoryItems->sum('quantity');
                $totalValue = $inventoryItems->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                });
                
                $lowStockItems = $inventoryItems->filter(function($item) {
                    return $item->quantity < $item->reorder_level && $item->quantity > 0;
                })->count();
                
                $outOfStockItems = $inventoryItems->filter(function($item) {
                    return $item->quantity <= 0;
                })->count();
                
                $inStockItems = $inventoryItems->filter(function($item) {
                    return $item->quantity >= $item->reorder_level;
                })->count();

                return [
                    'id' => $store->id,
                    'name' => $store->display_name,
                    'project' => $store->project,
                    'type' => $store->type,
                    'total_items' => $totalItems,
                    'total_quantity' => $totalQuantity,
                    'total_value' => $totalValue,
                    'low_stock_items' => $lowStockItems,
                    'out_of_stock_items' => $outOfStockItems,
                    'in_stock_items' => $inStockItems,
                    'is_main_store' => $store->isMainStore(),
                    'is_project_store' => $store->isProjectStore(),
                ];
            });

        // Overall inventory statistics
        $overallStats = [
            'total_stores' => $stores->count(),
            'total_inventory_items' => $stores->sum('total_items'),
            'total_inventory_value' => $stores->sum('total_value'),
            'total_low_stock_items' => $stores->sum('low_stock_items'),
            'total_out_of_stock_items' => $stores->sum('out_of_stock_items'),
            'avg_store_value' => $stores->avg('total_value'),
        ];

        // Recent stock movements across all stores
        $recentMovements = InventoryLog::with(['item.store.project', 'user'])
            ->latest()
            ->take(20)
            ->get();

        // Stock movement statistics
        $movementStats = [
            'total_movements' => InventoryLog::count(),
            'stock_ins' => InventoryLog::where('type', 'in')->count(),
            'stock_outs' => InventoryLog::where('type', 'out')->count(),
            'adjustments' => InventoryLog::where('type', 'adjustment')->count(),
            'total_value_moved' => InventoryLog::sum(DB::raw('quantity * unit_price')),
        ];

        // Top valuable items across all stores
        $topValuableItems = InventoryItem::with(['store.project'])
            ->select('*', DB::raw('quantity * unit_price as total_value'))
            ->orderBy('total_value', 'desc')
            ->take(10)
            ->get();

        // Category-wise inventory value
        $categoryStats = InventoryItem::select('category', DB::raw('COUNT(*) as item_count'), DB::raw('SUM(quantity * unit_price) as total_value'))
            ->groupBy('category')
            ->orderBy('total_value', 'desc')
            ->get();

        return view('ceo.inventory.index', compact(
            'stores',
            'overallStats',
            'recentMovements',
            'movementStats',
            'topValuableItems',
            'categoryStats'
        ));
    }

   public function storeDetail(Store $store)
{
    // Get detailed inventory for a specific store
    $inventoryItems = $store->inventoryItems()
        ->with(['logs' => function($query) {
            $query->latest()->take(5);
        }])
        ->orderBy('quantity', 'asc') // Show low stock first
        ->get();

    $storeStats = [
        'total_items' => $inventoryItems->count(),
        'total_quantity' => $inventoryItems->sum('quantity'),
        'total_value' => $inventoryItems->sum(function($item) {
            return $item->quantity * $item->unit_price;
        }),
        'low_stock_items' => $inventoryItems->filter(function($item) {
            return $item->quantity < $item->reorder_level && $item->quantity > 0;
        })->count(),
        'out_of_stock_items' => $inventoryItems->filter(function($item) {
            return $item->quantity <= 0;
        })->count(),
        'in_stock_items' => $inventoryItems->filter(function($item) {
            return $item->quantity >= $item->reorder_level;
        })->count(),
    ];

    // Recent movements for this store - with safe relationship handling
    $recentMovements = InventoryLog::whereHas('item', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })
        ->with(['item', 'user'])
        ->latest()
        ->take(20)
        ->get();

    return view('ceo.inventory.store_detail', compact(
        'store',
        'inventoryItems',
        'storeStats',
        'recentMovements'
    ));
}

    public function stockMovements(Request $request)
    {
        $query = InventoryLog::with(['item.store.project', 'user', 'project']);

        // Apply filters
        if ($request->store_id) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('store_id', $request->store_id);
            });
        }

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(50);
        $stores = Store::all();

        return view('ceo.inventory.movements', compact('movements', 'stores'));
    }

    public function exportInventoryReport()
    {
        $stores = Store::with(['project', 'inventoryItems'])->get();

        $filename = "ceo_inventory_report_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->streamDownload(function () use ($stores) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Store Name', 'Project', 'Type', 'Total Items', 'Total Quantity',
                'Total Value', 'Low Stock Items', 'Out of Stock Items', 'In Stock Items'
            ]);

            // Store data rows
            foreach ($stores as $store) {
                $inventoryItems = $store->inventoryItems;
                
                $totalItems = $inventoryItems->count();
                $totalQuantity = $inventoryItems->sum('quantity');
                $totalValue = $inventoryItems->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                });
                
                $lowStockItems = $inventoryItems->filter(function($item) {
                    return $item->quantity < $item->reorder_level && $item->quantity > 0;
                })->count();
                
                $outOfStockItems = $inventoryItems->filter(function($item) {
                    return $item->quantity <= 0;
                })->count();
                
                $inStockItems = $inventoryItems->filter(function($item) {
                    return $item->quantity >= $item->reorder_level;
                })->count();

                fputcsv($file, [
                    $store->display_name,
                    $store->project->name ?? 'N/A',
                    $store->isMainStore() ? 'Main Store' : 'Project Store',
                    $totalItems,
                    $totalQuantity,
                    $totalValue,
                    $lowStockItems,
                    $outOfStockItems,
                    $inStockItems
                ]);
            }

            // Summary section
            fputcsv($file, []); // Empty row
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, [
                'Total Stores',
                'Total Inventory Value',
                'Average Store Value',
                'Total Low Stock Items',
                'Total Out of Stock Items'
            ]);
            
            $totalStores = $stores->count();
            $totalInventoryValue = $stores->sum(function($store) use ($inventoryItems) {
                return $store->inventoryItems->sum(function($item) {
                    return $item->quantity * $item->unit_price;
                });
            });
            $avgStoreValue = $totalStores > 0 ? $totalInventoryValue / $totalStores : 0;
            $totalLowStock = $stores->sum('low_stock_items');
            $totalOutOfStock = $stores->sum('out_of_stock_items');

            fputcsv($file, [
                $totalStores,
                $totalInventoryValue,
                $avgStoreValue,
                $totalLowStock,
                $totalOutOfStock
            ]);

            fclose($file);
        }, $filename, $headers);
    }
}