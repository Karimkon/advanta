<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\InventoryItem;
use App\Models\StoreRelease;
use App\Models\Requisition;
use App\Models\Lpo;
use Illuminate\Http\Request;

class StoresDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get stores accessible by this user
        $stores = Store::with(['project', 'project.users'])
            ->where(function($query) use ($user) {
                // Main store - only for main store manager (user ID 6)
                if ($user->id === 6) {
                    $query->where('type', Store::TYPE_MAIN);
                } else {
                    // Project stores - for users assigned to those projects
                    $query->where('type', Store::TYPE_PROJECT)
                          ->whereHas('project.users', function($projectQuery) use ($user) {
                              $projectQuery->where('user_id', $user->id);
                          });
                }
            })
            ->get();

        // If no stores found for this user
        if ($stores->isEmpty()) {
            return view('stores.dashboard', compact('stores'))
                ->with('error', 'No stores assigned to your account. Please contact administration.');
        }

        // Use the first store for this user
        $currentStore = $stores->first();

        // Get store statistics
        $stats = [
            'total_items' => $currentStore->getTotalItemsCount(),
            'total_quantity' => $currentStore->getTotalQuantity(),
            'store_value' => $currentStore->getStoreValue(),
            'low_stock_items' => $currentStore->getLowStockItems()->count(),
            'out_of_stock_items' => $currentStore->getOutOfStockItems()->count(),
        ];

        // Get inventory items for this store
        $inventoryItems = $currentStore->inventoryItems()
            ->with(['logs' => function($query) {
                $query->latest()->take(3);
            }])
            ->latest()
            ->take(10)
            ->get();

        // Get low stock items
        $lowStockItems = $currentStore->getLowStockItems()
            ->take(5);

        // Get pending store requisitions for this store
        $pendingRequisitions = Requisition::where('store_id', $currentStore->id)
            ->where('type', Requisition::TYPE_STORE)
            ->where('status', Requisition::STATUS_PROJECT_MANAGER_APPROVED)
            ->with(['project', 'requester', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // Get recent store releases
        $recentReleases = StoreRelease::where('store_id', $currentStore->id)
            ->with(['requisition', 'requisition.project'])
            ->latest()
            ->take(5)
            ->get();

        // Get pending LPOs count
        $pendingLposCount = 0;
        if ($currentStore->project_id) {
            $pendingLposCount = Lpo::whereHas('requisition', function($query) use ($currentStore) {
                    $query->where('project_id', $currentStore->project_id);
                })
                ->where('status', 'issued')
                ->count();
        }

        // Share counts with ALL store views (for layout)
        view()->share('pendingCount', $pendingRequisitions->count());
        view()->share('pendingLposCount', $pendingLposCount); // ADD THIS LINE
        view()->share('stores', $stores); // ADD THIS LINE

        return view('stores.dashboard', compact(
            'stores',
            'currentStore',
            'stats',
            'inventoryItems',
            'lowStockItems',
            'pendingRequisitions',
            'recentReleases',
            'pendingLposCount'
        ));
    }
}