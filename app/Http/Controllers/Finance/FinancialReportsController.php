<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Requisition;

class FinancialReportsController extends Controller
{
    public function index()
    {
        // Overall financial summary
        $totalPayments = Payment::sum('total_amount');
        $totalExpenses = Expense::sum('amount');
        $pendingPayments = Requisition::where('status', 'ceo_approved')->count();

        // Monthly trends
        $monthlyPayments = Payment::selectRaw('YEAR(created_at) year, MONTH(created_at) month, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get();

        $monthlyExpenses = Expense::selectRaw('YEAR(expense_date) year, MONTH(expense_date) month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get();

        // Project-wise breakdown
        $projectBreakdown = Project::with(['payments', 'expenses'])->get()->map(function ($project) {
            return [
                'name' => $project->name,
                'total_payments' => $project->payments->sum('total_amount'),
                'total_expenses' => $project->expenses->sum('amount'),
                'net_total' => $project->payments->sum('total_amount') + $project->expenses->sum('amount')
            ];
        });

        return view('finance.reports.index', compact(
            'totalPayments',
            'totalExpenses',
            'pendingPayments',
            'monthlyPayments',
            'monthlyExpenses',
            'projectBreakdown'
        ));
    }

    public function projectReport($projectId)
    {
        $project = Project::with(['payments', 'expenses', 'requisitions'])->findOrFail($projectId);

        $paymentsByMonth = $project->payments()
            ->selectRaw('YEAR(payment_date) year, MONTH(payment_date) month, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $expensesByCategory = $project->expenses()
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        return view('finance.reports.project', compact(
            'project',
            'paymentsByMonth',
            'expensesByCategory'
        ));
    }

    public function exportFinancialSummary()
    {
        $projects = Project::with(['payments', 'expenses'])->get();

        return response()->streamDownload(function () use ($projects) {
            echo "Project,Total Payments,Total Expenses,Net Total\n";
            foreach ($projects as $project) {
                $totalPayments = $project->payments->sum('total_amount');
                $totalExpenses = $project->expenses->sum('amount');
                $netTotal = $totalPayments + $totalExpenses;
                
                echo "{$project->name},{$totalPayments},{$totalExpenses},{$netTotal}\n";
            }
        }, 'financial_summary_' . date('Y-m-d') . '.csv');
    }
}