<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\Lpo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportsController extends Controller
{
   public function index()
{
    try {
        // Overall financial summary
        $totalPayments = Payment::sum('amount') ?? 0;
        $totalExpenses = Expense::sum('amount') ?? 0;
        
        $pendingPayments = Requisition::where('status', 'delivered')
            ->whereHas('lpo', function($query) {
                $query->where('status', 'delivered');
            })
            ->count();

        // Project-wise spending using raw query
        $projectSpending = DB::table('projects')
            ->leftJoin('requisitions', 'projects.id', '=', 'requisitions.project_id')
            ->leftJoin('lpos', 'requisitions.id', '=', 'lpos.requisition_id')
            ->leftJoin('payments', 'lpos.id', '=', 'payments.lpo_id')
            ->leftJoin('expenses', 'projects.id', '=', 'expenses.project_id')
            ->select(
                'projects.id',
                'projects.name',
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_payments'),
                DB::raw('COALESCE(SUM(expenses.amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(payments.amount), 0) + COALESCE(SUM(expenses.amount), 0) as total_spent')
            )
            ->groupBy('projects.id', 'projects.name')
            ->having('total_spent', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->get();

        // Payment methods breakdown
        $paymentMethods = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();

        // Recent financial activities
        $recentPayments = Payment::with(['supplier', 'paidBy'])
            ->whereNotNull('amount')
            ->latest()
            ->take(10)
            ->get();

        $recentExpenses = Expense::with(['project'])
            ->whereNotNull('amount')
            ->latest()
            ->take(10)
            ->get();

        return view('finance.reports.index', compact(
            'totalPayments',
            'totalExpenses',
            'pendingPayments',
            'projectSpending',
            'paymentMethods',
            'recentPayments',
            'recentExpenses'
        ));

    } catch (\Exception $e) {
        \Log::error('Financial Reports Error: ' . $e->getMessage());
        
        return view('finance.reports.index', [
            'totalPayments' => 0,
            'totalExpenses' => 0,
            'pendingPayments' => 0,
            'projectSpending' => collect(),
            'paymentMethods' => collect(),
            'recentPayments' => collect(),
            'recentExpenses' => collect(),
        ])->with('error', 'Error loading reports: ' . $e->getMessage());
    }
}

    public function projectReport($projectId)
    {
        try {
            $project = Project::with(['requisitions.lpo.payments', 'expenses'])->findOrFail($projectId);

            // Calculate project payments through requisitions and LPOs
            $projectPayments = 0;
            foreach ($project->requisitions as $requisition) {
                if ($requisition->lpo && $requisition->lpo->payments) {
                    $projectPayments += $requisition->lpo->payments->sum('amount');
                }
            }

            $projectExpenses = $project->expenses->sum('amount');

            // Monthly data for the project
            $monthlyData = Payment::whereHas('lpo.requisition', function($query) use ($projectId) {
                    $query->where('project_id', $projectId);
                })
                ->selectRaw('YEAR(COALESCE(paid_on, created_at)) as year, MONTH(COALESCE(paid_on, created_at)) as month, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return view('finance.reports.project', compact(
                'project',
                'projectPayments',
                'projectExpenses',
                'monthlyData'
            ));

        } catch (\Exception $e) {
            return redirect()->route('finance.reports.index')
                ->with('error', 'Project report not found: ' . $e->getMessage());
        }
    }

    public function exportFinancialSummary()
    {
        try {
            // Use direct queries for export to ensure data consistency
            $totalPayments = Payment::sum('amount');
            $totalExpenses = Expense::sum('amount');
            
            $paymentMethods = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get();

            $projects = Project::all();
            $projectData = [];
            
            foreach ($projects as $project) {
                // Calculate payments for this project
                $projectPayments = DB::table('payments')
                    ->join('lpos', 'payments.lpo_id', '=', 'lpos.id')
                    ->join('requisitions', 'lpos.requisition_id', '=', 'requisitions.id')
                    ->where('requisitions.project_id', $project->id)
                    ->sum('payments.amount');

                $projectExpenses = Expense::where('project_id', $project->id)->sum('amount');
                
                $projectData[] = [
                    'name' => $project->name,
                    'payments' => $projectPayments,
                    'expenses' => $projectExpenses,
                    'total' => $projectPayments + $projectExpenses
                ];
            }

            return response()->streamDownload(function () use ($totalPayments, $totalExpenses, $paymentMethods, $projectData) {
                echo "Financial Summary Report\n";
                echo "Generated on: " . now()->format('Y-m-d H:i:s') . "\n\n";
                
                echo "OVERALL SUMMARY\n";
                echo "Total Payments: UGX " . number_format($totalPayments, 2) . "\n";
                echo "Total Expenses: UGX " . number_format($totalExpenses, 2) . "\n";
                echo "Net Total: UGX " . number_format($totalPayments + $totalExpenses, 2) . "\n\n";
                
                echo "PAYMENT METHODS BREAKDOWN\n";
                foreach ($paymentMethods as $method) {
                    echo $method->payment_method . ": UGX " . number_format($method->total, 2) . " (" . $method->count . " payments)\n";
                }
                echo "\n";
                
                echo "PROJECT SPENDING\n";
                foreach ($projectData as $project) {
                    echo $project['name'] . ": UGX " . number_format($project['total'], 2) . 
                         " (Payments: UGX " . number_format($project['payments'], 2) . 
                         ", Expenses: UGX " . number_format($project['expenses'], 2) . ")\n";
                }
            }, 'financial_summary_' . date('Y-m-d') . '.txt');

        } catch (\Exception $e) {
            return redirect()->route('finance.reports.index')
                ->with('error', 'Failed to export report: ' . $e->getMessage());
        }
    }
}