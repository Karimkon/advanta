<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Requisition;
use App\Models\Project;
use App\Models\Lpo;
use Illuminate\Support\Facades\DB;

class CEOFinancialReportsController extends Controller
{
    public function index()
    {
        try {
            // Overall financial summary
            $totalPayments = Payment::sum('amount') ?? 0;
            $totalExpenses = Expense::sum('amount') ?? 0;
            
            // Pending payments for approval
            $pendingPayments = Requisition::where('status', 'delivered')
                ->whereHas('lpo', function($query) {
                    $query->where('status', 'delivered');
                })
                ->count();

            // Project-wise spending using raw query for better performance
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

            // Monthly trends
            $monthlyPayments = Payment::selectRaw('YEAR(COALESCE(paid_on, created_at)) as year, MONTH(COALESCE(paid_on, created_at)) as month, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(6)
                ->get();

            // CEO-specific stats
            $ceoStats = [
                'total_approved_requisitions' => Requisition::where('status', 'ceo_approved')->count(),
                'total_approved_lpos' => Lpo::where('status', 'issued')->count(),
                'pending_ceo_approval' => Requisition::where('status', 'procurement')->count(),
            ];

            return view('ceo.reports.index', compact(
                'totalPayments',
                'totalExpenses',
                'pendingPayments',
                'projectSpending',
                'paymentMethods',
                'recentPayments',
                'recentExpenses',
                'monthlyPayments',
                'ceoStats'
            ));

        } catch (\Exception $e) {
            \Log::error('CEO Financial Reports Error: ' . $e->getMessage());
            
            return view('ceo.reports.index', [
                'totalPayments' => 0,
                'totalExpenses' => 0,
                'pendingPayments' => 0,
                'projectSpending' => collect(),
                'paymentMethods' => collect(),
                'recentPayments' => collect(),
                'recentExpenses' => collect(),
                'monthlyPayments' => collect(),
                'ceoStats' => [
                    'total_approved_requisitions' => 0,
                    'total_approved_lpos' => 0,
                    'pending_ceo_approval' => 0,
                ],
            ])->with('error', 'Error loading financial reports: ' . $e->getMessage());
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

            // Project requisitions summary
            $requisitionsSummary = $project->requisitions()
                ->selectRaw('status, COUNT(*) as count, SUM(estimated_total) as total')
                ->groupBy('status')
                ->get();

            return view('ceo.reports.project', compact(
                'project',
                'projectPayments',
                'projectExpenses',
                'monthlyData',
                'requisitionsSummary'
            ));

        } catch (\Exception $e) {
            return redirect()->route('ceo.reports.index')
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

            // CEO-specific data
            $ceoApprovals = Requisition::where('status', 'ceo_approved')->count();
            $ceoLPOs = Lpo::where('status', 'issued')->count();

            return response()->streamDownload(function () use ($totalPayments, $totalExpenses, $paymentMethods, $projectData, $ceoApprovals, $ceoLPOs) {
                echo "CEO FINANCIAL SUMMARY REPORT\n";
                echo "Generated on: " . now()->format('Y-m-d H:i:s') . "\n";
                echo "Report Type: Executive Financial Overview\n\n";
                
                echo "EXECUTIVE SUMMARY\n";
                echo "================\n";
                echo "Total Payments: UGX " . number_format($totalPayments, 2) . "\n";
                echo "Total Expenses: UGX " . number_format($totalExpenses, 2) . "\n";
                echo "Net Total: UGX " . number_format($totalPayments + $totalExpenses, 2) . "\n";
                echo "CEO Approved Requisitions: " . $ceoApprovals . "\n";
                echo "CEO Approved LPOs: " . $ceoLPOs . "\n\n";
                
                echo "PAYMENT METHODS BREAKDOWN\n";
                echo "========================\n";
                foreach ($paymentMethods as $method) {
                    echo str_pad(ucfirst(str_replace('_', ' ', $method->payment_method)), 15) . ": " . 
                         "UGX " . str_pad(number_format($method->total, 2), 15) . 
                         " (" . $method->count . " payments)\n";
                }
                echo "\n";
                
                echo "PROJECT SPENDING ANALYSIS\n";
                echo "========================\n";
                foreach ($projectData as $project) {
                    echo str_pad($project['name'], 25) . ": UGX " . 
                         str_pad(number_format($project['total'], 2), 15) . 
                         " (Payments: UGX " . str_pad(number_format($project['payments'], 2), 12) . 
                         ", Expenses: UGX " . str_pad(number_format($project['expenses'], 2), 12) . ")\n";
                }
                
                echo "\nRECOMMENDATIONS\n";
                echo "===============\n";
                echo "1. Review high-spending projects for cost optimization\n";
                echo "2. Monitor payment method trends for cash flow management\n";
                echo "3. Analyze project ROI based on spending patterns\n";
                echo "4. Consider budget adjustments for upcoming projects\n";

            }, 'ceo_financial_summary_' . date('Y-m-d') . '.txt');

        } catch (\Exception $e) {
            return redirect()->route('ceo.reports.index')
                ->with('error', 'Failed to export report: ' . $e->getMessage());
        }
    }

    public function requisitionsReport()
    {
        try {
            $requisitions = Requisition::with(['project', 'requester', 'lpo.supplier', 'items'])
                ->latest()
                ->paginate(20);

            $requisitionStats = DB::table('requisitions')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending')
                ->selectRaw('COUNT(CASE WHEN status = "ceo_approved" THEN 1 END) as ceo_approved')
                ->selectRaw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed')
                ->selectRaw('SUM(estimated_total) as total_value')
                ->first();

            return view('ceo.reports.requisitions', compact(
                'requisitions',
                'requisitionStats'
            ));

        } catch (\Exception $e) {
            return redirect()->route('ceo.reports.index')
                ->with('error', 'Error loading requisitions report: ' . $e->getMessage());
        }
    }
}