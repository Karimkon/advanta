<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Requisition;
use App\Models\Lpo;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Payment;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'today'); // 'today'|'week'|'month'
        $today = Carbon::today();

        switch ($filter) {
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $title = 'This Week';
                break;
            case 'month':
                $from = Carbon::now()->startOfMonth();
                $title = 'This Month';
                break;
            default:
                $from = $today->startOfDay();
                $title = 'Today';
        }

        $to = Carbon::now()->endOfDay();

        // Core statistics - FIXED: Use correct status constants
        $stats = [
            'projects_count' => Project::count(),
            'requisitions_pending' => Requisition::where('status', Requisition::STATUS_PENDING)->count(),
            'requisitions_approved' => Requisition::whereIn('status', [
                Requisition::STATUS_PROJECT_MANAGER_APPROVED,
                Requisition::STATUS_OPERATIONS_APPROVED,
                Requisition::STATUS_CEO_APPROVED
            ])->count(),
            'requisitions_rejected' => Requisition::where('status', Requisition::STATUS_REJECTED)->count(),
            'lpos_issued' => Lpo::count(),
            'users_total' => User::count(),
            'suppliers_total' => Supplier::count(),
            'active_projects' => Project::where('status', 'active')->count(),
        ];

        // Financial stats for the period
        $financials = [
            'total_payments' => Payment::whereBetween('created_at', [$from, $to])->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->whereBetween('created_at', [$from, $to])->sum('amount'),
            'completed_payments' => Payment::where('status', 'completed')->whereBetween('created_at', [$from, $to])->sum('amount'),
        ];

        // Inventory stats
        $inventory = [
            'total_items' => InventoryItem::count(),
            'low_stock_items' => InventoryItem::where('quantity', '<', \DB::raw('reorder_level'))->count(),
            'out_of_stock_items' => InventoryItem::where('quantity', '<=', 0)->count(),
        ];

        // ✅ FIXED: Recent activity - properly load requester relationship
        $recentRequisitions = Requisition::with(['project', 'requester'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentProjects = Project::with(['users'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart data - Last 7 days requisitions
        $chartDays = 7;
        $chartFrom = Carbon::now()->subDays($chartDays - 1)->startOfDay();
        
        $requisitionsPerDay = Requisition::selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->whereDate('created_at', '>=', $chartFrom->toDateString())
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->pluck('count', 'day')
            ->toArray();

        $labels = [];
        $requisitionSeries = [];
        for ($i = 0; $i < $chartDays; $i++) {
            $d = Carbon::now()->subDays($chartDays - 1 - $i)->format('Y-m-d');
            $labels[] = Carbon::parse($d)->format('M d');
            $requisitionSeries[] = $requisitionsPerDay[$d] ?? 0;
        }

        return view('admin.dashboard', compact(
            'stats',
            'financials',
            'inventory',
            'recentRequisitions',
            'recentProjects',
            'filter',
            'title',
            'labels',
            'requisitionSeries'
        ));
    }
}