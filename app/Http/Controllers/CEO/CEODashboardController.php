<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Lpo;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CEODashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $projectMilestones = $this->getProjectMilestonesOverview();
        $attentionMilestones = $this->getAttentionMilestones();

        
        // Get requisitions pending CEO approval
        $pendingRequisitions = Requisition::where('status', Requisition::STATUS_PROCUREMENT)
            ->with(['project', 'requester', 'items', 'lpo', 'lpo.supplier'])
            ->latest()
            ->take(5)
            ->get();

        // Get LPOs pending CEO approval
        $pendingLpos = Lpo::where('status', 'draft')
            ->whereHas('requisition', function($query) {
                $query->where('status', Requisition::STATUS_CEO_APPROVED);
            })
            ->with(['requisition', 'supplier', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // Get comprehensive statistics
        $stats = DB::table('requisitions')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = "procurement" THEN 1 END) as pending_approval')
            ->selectRaw('COUNT(CASE WHEN status = "ceo_approved" THEN 1 END) as ceo_approved')
            ->selectRaw('COUNT(CASE WHEN status = "lpo_issued" THEN 1 END) as lpo_issued')
            ->selectRaw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed')
            ->selectRaw('SUM(estimated_total) as total_amount')
            ->first();

        // Get recent approvals by CEO
        $recentApprovals = Requisition::whereHas('approvals', function($query) use ($user) {
            $query->where('approved_by', $user->id)
                  ->where('role', 'ceo');
        })
        ->with(['project', 'requester'])
        ->latest()
        ->take(5)
        ->get();

        // Financial Overview
        $financialStats = $this->getFinancialStats();
        
        // Project Performance - Use safer query
        $projectPerformance = $this->getProjectPerformance();
        
        // Recent Financial Activities
        $recentFinancialActivities = $this->getRecentFinancialActivities();

        // Monthly Spending Trends
        $monthlyTrends = $this->getMonthlyTrends();

        // Share pending count with layout
        view()->share('pendingCount', $pendingRequisitions->count() + $pendingLpos->count());

        return view('ceo.dashboard', compact(
            'pendingRequisitions',
            'pendingLpos',
            'stats',
            'recentApprovals',
            'financialStats',
            'projectPerformance',
            'recentFinancialActivities',
            'monthlyTrends',
            'projectMilestones',    
            'attentionMilestones'
        ));
    }

    private function getFinancialStats()
    {
        return [
            'total_payments' => Payment::sum('amount') ?? 0,
            'total_expenses' => Expense::sum('amount') ?? 0,
            'pending_payments' => Requisition::where('status', 'delivered')
                ->whereHas('lpo', function($query) {
                    $query->where('status', 'delivered');
                })
                ->sum('estimated_total') ?? 0,
            'this_month_spending' => Payment::whereMonth('created_at', now()->month)
                ->sum('amount') ?? 0,
            'avg_requisition_value' => Requisition::where('status', 'completed')
                ->avg('estimated_total') ?? 0,
        ];
    }

    private function getProjectPerformance()
    {
        try {
            // Use a safer approach with direct queries
            $projects = Project::all();
            
            return $projects->map(function($project) {
                // Calculate project payments through LPOs
                $projectPayments = DB::table('payments')
                    ->join('lpos', 'payments.lpo_id', '=', 'lpos.id')
                    ->join('requisitions', 'lpos.requisition_id', '=', 'requisitions.id')
                    ->where('requisitions.project_id', $project->id)
                    ->sum('payments.amount');

                $projectExpenses = Expense::where('project_id', $project->id)->sum('amount');
                $totalSpent = $projectPayments + $projectExpenses;

                // Get requisition count
                $requisitionCount = $project->requisitions()->count();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'total_spent' => $totalSpent,
                    'payment_ratio' => $totalSpent > 0 ? ($projectPayments / $totalSpent) * 100 : 0,
                    'requisition_count' => $requisitionCount,
                ];
            })
            ->where('total_spent', '>', 0)
            ->sortByDesc('total_spent')
            ->take(5)
            ->values();

        } catch (\Exception $e) {
            \Log::error('Project Performance Error: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }

    private function getRecentFinancialActivities()
    {
        try {
            $payments = Payment::with(['supplier'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function($payment) {
                    return [
                        'type' => 'payment',
                        'description' => 'Payment to ' . ($payment->supplier->name ?? 'Supplier'),
                        'amount' => $payment->amount,
                        'date' => $payment->paid_on ?? $payment->created_at,
                        'color' => 'success',
                        'icon' => 'bi-credit-card'
                    ];
                });

            $expenses = Expense::with(['project'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function($expense) {
                    return [
                        'type' => 'expense',
                        'description' => $expense->description,
                        'amount' => $expense->amount,
                        'date' => $expense->created_at,
                        'color' => 'danger',
                        'icon' => 'bi-cash-coin'
                    ];
                });

            return $payments->merge($expenses)
                ->sortByDesc('date')
                ->take(6);

        } catch (\Exception $e) {
            \Log::error('Recent Activities Error: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }

    private function getMonthlyTrends()
    {
        try {
            return Payment::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'period' => Carbon::create($item->year, $item->month)->format('M Y'),
                        'amount' => $item->total,
                        'trend' => $item->total > 0 ? 'up' : 'stable'
                    ];
                });

        } catch (\Exception $e) {
            \Log::error('Monthly Trends Error: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }

    private function getProjectMilestonesOverview()
{
    return Project::with(['milestones' => function($query) {
            $query->where('status', '!=', 'completed')
                  ->orderBy('due_date');
        }])
        ->whereHas('milestones')
        ->get()
        ->map(function($project) {
            $totalMilestones = $project->milestones->count();
            $completedMilestones = $project->milestones->where('status', 'completed')->count();
            $overdueMilestones = $project->milestones->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count();
            
            return [
                'id' => $project->id,
                'name' => $project->name,
                'total_milestones' => $totalMilestones,
                'completed_milestones' => $completedMilestones,
                'overdue_milestones' => $overdueMilestones,
                'completion_rate' => $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0,
                'latest_milestone' => $project->milestones->sortByDesc('due_date')->first(),
                'status' => $project->status,
            ];
        })
        ->sortByDesc('completion_rate')
        ->take(5)
        ->values();
}

private function getAttentionMilestones()
{
    return \App\Models\ProjectMilestone::with(['project'])
        ->where('due_date', '<=', now()->addDays(7))
        ->where('status', '!=', 'completed')
        ->orderBy('due_date')
        ->take(5)
        ->get();
}
}