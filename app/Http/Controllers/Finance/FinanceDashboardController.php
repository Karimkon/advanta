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
                ->with(['project', 'lpo.supplier', 'lpo'])
                ->latest()
                ->paginate(10);

            // Get recent payments with proper relationships
            $recentPayments = Payment::with(['supplier', 'paidBy'])
                ->whereNotNull('paid_on') // Only include payments with dates
                ->latest()
                ->take(5)
                ->get();

            // Calculate financial stats correctly
            $stats = [
                'pending_payments' => Requisition::where('status', 'delivered')
                                    ->whereHas('lpo', function($query) {
                                        $query->where('status', 'delivered');
                                    })
                                    ->count(),
                'total_payments_this_month' => Payment::whereMonth('paid_on', now()->month)
                                            ->whereNotNull('paid_on')
                                            ->count(),
                'total_amount_this_month' => Payment::whereMonth('paid_on', now()->month)
                                          ->whereNotNull('paid_on')
                                          ->sum('amount') ?? 0,
                'overdue_payments' => 0, // You can implement overdue logic later
                'total_expenses' => Expense::sum('amount') ?? 0,
            ];

            // Get project spending data - FIXED
            $projects = Project::all();
            $projectSpending = $projects->map(function($project) {
                // Calculate total payments for this project through requisitions
                $projectPayments = Payment::whereHas('lpo.requisition', function($query) use ($project) {
                    $query->where('project_id', $project->id);
                })->sum('amount');
                
                // Calculate total expenses for this project
                $projectExpenses = Expense::where('project_id', $project->id)->sum('amount');
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'total_payments' => $projectPayments ?? 0,
                    'total_expenses' => $projectExpenses ?? 0,
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
            \Log::error('Finance Dashboard Error: ' . $e->getMessage());
            
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