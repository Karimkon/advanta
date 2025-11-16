<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Lpo;

class FinanceDashboardController extends Controller
{
    public function index()
{
    try {
        // Get pending payments for finance approval
        $pendingPayments = Requisition::where('status', 'delivered')
            ->whereHas('lpo', function($query) {
                $query->where('status', 'delivered');
            })
            ->with(['project', 'lpo.supplier', 'lpo']) // Fixed: supplier through LPO
            ->latest()
            ->paginate(10);

        // Get recent payments
        $recentPayments = Payment::with(['supplier', 'paidBy'])
            ->latest()
            ->take(5)
            ->get();

        // Get financial stats
        $stats = [
            'pending_payments' => Requisition::where('status', 'delivered')
                                ->whereHas('lpo', function($query) {
                                    $query->where('status', 'delivered');
                                })
                                ->count(),
            'total_payments_this_month' => Payment::whereMonth('paid_on', now()->month)->count(),
            'total_amount_this_month' => Payment::whereMonth('paid_on', now()->month)->sum('amount'),
            'overdue_payments' => 0,
            'total_expenses' => Expense::sum('amount') ?? 0,
        ];

        // Get project spending data
        $projects = Project::all();
        $projectSpending = $projects->map(function($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'total_payments' => 0,
                'total_expenses' => Expense::where('project_id', $project->id)->sum('amount'),
            ];
        });

        return view('finance.dashboard', compact(
            'pendingPayments', 
            'recentPayments', 
            'stats',
            'projectSpending',
            'projects'
        ));

    } catch (\Exception $e) {
        return view('finance.dashboard', [
            'pendingPayments' => collect(),
            'recentPayments' => collect(),
            'stats' => [
                'pending_payments' => 0,
                'total_payments_this_month' => 0,
                'total_amount_this_month' => 0,
                'overdue_payments' => 0,
                'total_expenses' => 0,
            ],
            'projectSpending' => collect(),
            'projects' => collect(),
        ]);
    }
}
}