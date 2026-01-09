<?php

namespace App\Providers;

use App\Models\Store;
use App\Models\Requisition;
use App\Models\Lpo;
use App\Models\StoreRelease;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share notifications with all store views
        View::composer('stores.*', function ($view) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            // Skip if notifications already set (from dashboard controller)
            if ($view->offsetExists('notifications')) {
                return;
            }

            // Get user's stores
            $stores = Store::with(['project', 'project.users'])
                ->where(function($query) use ($user) {
                    if ($user->id === 6) {
                        $query->where('type', Store::TYPE_MAIN);
                    } else {
                        $query->where('type', Store::TYPE_PROJECT)
                              ->whereHas('project.users', function($projectQuery) use ($user) {
                                  $projectQuery->where('user_id', $user->id);
                              });
                    }
                })
                ->get();

            if ($stores->isEmpty()) {
                return;
            }

            $currentStore = $stores->first();

            // Get stats
            $lowStockCount = $currentStore->inventoryItems()
                ->where('quantity', '<', DB::raw('reorder_level'))
                ->count();

            $outOfStockCount = $currentStore->inventoryItems()
                ->where('quantity', '<=', 0)
                ->count();

            // Get pending requisitions count
            $pendingRequisitionsCount = Requisition::where('store_id', $currentStore->id)
                ->where('type', Requisition::TYPE_STORE)
                ->where('status', Requisition::STATUS_PROJECT_MANAGER_APPROVED)
                ->count();

            // Get pending LPOs count
            $pendingLposCount = 0;
            if ($currentStore->project_id) {
                $pendingLposCount = Lpo::whereHas('requisition', function($query) use ($currentStore) {
                        $query->where('project_id', $currentStore->project_id);
                    })
                    ->where('status', 'issued')
                    ->count();
            }

            // Today's releases
            $releasesToday = StoreRelease::where('store_id', $currentStore->id)
                ->whereDate('created_at', today())
                ->count();

            // Build notifications array
            $notifications = [
                'pending_requisitions' => $pendingRequisitionsCount,
                'pending_lpos' => $pendingLposCount,
                'low_stock' => $lowStockCount,
                'out_of_stock' => $outOfStockCount,
                'recent_releases_today' => $releasesToday,
            ];
            $notifications['total'] = $notifications['pending_requisitions']
                + $notifications['pending_lpos']
                + $notifications['low_stock']
                + $notifications['out_of_stock'];

            // Share with view
            $view->with('notifications', $notifications);
            $view->with('stores', $stores);

            // Only set currentStore if not already set
            if (!$view->offsetExists('currentStore')) {
                $view->with('currentStore', $currentStore);
            }
        });
    }
}
