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
            // Get pending payments for finance approval WITH VAT-INCLUSIVE CALCULATIONS
            $pendingPayments = Requisition::where('status', 'delivered')
                ->whereHas('lpo', function($query) {
                    $query->where('status', 'delivered');
                })
                ->with(['project', 'lpo.supplier', 'lpo', 'lpo.items'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($requisition) {
                    // Calculate VAT-inclusive amount from LPO
                    $vatInclusiveTotal = 0;
                    $baseAmount = 0;
                    $vatAmount = 0;
                    
                    if ($requisition->lpo) {
                        // Use LPO total if available (should include VAT)
                        if ($requisition->lpo->total > 0) {
                            $vatInclusiveTotal = $requisition->lpo->total;
                            $baseAmount = $requisition->lpo->subtotal;
                            $vatAmount = $requisition->lpo->vat_amount;
                        } 
                        // Calculate from LPO items if needed
                        elseif ($requisition->lpo->items && $requisition->lpo->items->count() > 0) {
                            foreach ($requisition->lpo->items as $item) {
                                $itemTotal = $item->quantity * $item->unit_price;
                                $baseAmount += $itemTotal;
                                
                                if ($item->has_vat) {
                                    $itemVat = $itemTotal * ($item->vat_rate / 100);
                                    $vatAmount += $itemVat;
                                }
                            }
                            $vatInclusiveTotal = $baseAmount + $vatAmount;
                        }
                    }
                    
                    // Fallback to estimated_total if no LPO data
                    if ($vatInclusiveTotal == 0) {
                        $vatInclusiveTotal = $requisition->estimated_total;
                        $baseAmount = $requisition->estimated_total;
                    }
                    
                    // Add calculated amounts to requisition object for view
                    $requisition->vat_inclusive_total = $vatInclusiveTotal;
                    $requisition->base_amount = $baseAmount;
                    $requisition->vat_amount = $vatAmount;
                    
                    return $requisition;
                });

            // Get recent payments with proper relationships - CALCULATE VAT BREAKDOWN
            $recentPayments = Payment::with(['supplier', 'paidBy', 'lpo'])
                ->whereNotNull('paid_on')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($payment) {
                    // Calculate base amount (amount without VAT)
                    $payment->base_amount = $payment->amount - $payment->vat_amount;
                    return $payment;
                });

            // Calculate financial stats with VAT breakdown
            $currentMonthPayments = Payment::whereMonth('paid_on', now()->month)
                ->whereNotNull('paid_on')
                ->get();

            // Calculate pending payments totals with VAT
            $pendingRequisitions = Requisition::where('status', 'delivered')
                ->whereHas('lpo', function($query) {
                    $query->where('status', 'delivered');
                })
                ->with(['lpo'])
                ->get();
            
            $pendingTotalVatInclusive = 0;
            $pendingTotalBase = 0;
            $pendingTotalVat = 0;
            
            foreach ($pendingRequisitions as $req) {
                if ($req->lpo) {
                    $pendingTotalVatInclusive += $req->lpo->total;
                    $pendingTotalBase += $req->lpo->subtotal;
                    $pendingTotalVat += $req->lpo->vat_amount;
                } else {
                    $pendingTotalVatInclusive += $req->estimated_total;
                    $pendingTotalBase += $req->estimated_total;
                }
            }

            $stats = [
                'pending_payments' => $pendingRequisitions->count(),
                'pending_total_vat_inclusive' => $pendingTotalVatInclusive,
                'pending_total_base' => $pendingTotalBase,
                'pending_total_vat' => $pendingTotalVat,
                'total_payments_this_month' => $currentMonthPayments->count(),
                'total_amount_this_month' => $currentMonthPayments->sum('amount') ?? 0,
                'total_vat_this_month' => $currentMonthPayments->sum('vat_amount') ?? 0,
                'base_amount_this_month' => $currentMonthPayments->sum('amount') - $currentMonthPayments->sum('vat_amount'),
                'overdue_payments' => 0,
                'total_expenses' => Expense::sum('amount') ?? 0,
            ];

            // Get project spending data with VAT breakdown
            $projects = Project::all();
            $projectSpending = $projects->map(function($project) {
                // Get all payments for this project
                $projectPayments = Payment::whereHas('lpo.requisition', function($query) use ($project) {
                    $query->where('project_id', $project->id);
                })->get();
                
                $totalPayments = $projectPayments->sum('amount');
                $totalVat = $projectPayments->sum('vat_amount');
                $basePayments = $totalPayments - $totalVat;
                
                // Calculate pending payments for this project
                $pendingProjectReqs = Requisition::where('project_id', $project->id)
                    ->where('status', 'delivered')
                    ->whereHas('lpo', function($query) {
                        $query->where('status', 'delivered');
                    })
                    ->with(['lpo'])
                    ->get();
                
                $pendingProjectTotal = 0;
                $pendingProjectBase = 0;
                $pendingProjectVat = 0;
                
                foreach ($pendingProjectReqs as $req) {
                    if ($req->lpo) {
                        $pendingProjectTotal += $req->lpo->total;
                        $pendingProjectBase += $req->lpo->subtotal;
                        $pendingProjectVat += $req->lpo->vat_amount;
                    } else {
                        $pendingProjectTotal += $req->estimated_total;
                        $pendingProjectBase += $req->estimated_total;
                    }
                }
                
                // Calculate total expenses for this project
                $projectExpenses = Expense::where('project_id', $project->id)->sum('amount');
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'total_payments' => $totalPayments,
                    'total_base_payments' => $basePayments,
                    'total_vat_payments' => $totalVat,
                    'pending_payments_total' => $pendingProjectTotal,
                    'pending_payments_base' => $pendingProjectBase,
                    'pending_payments_vat' => $pendingProjectVat,
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
            \Log::error($e->getTraceAsString());
            
            return view('finance.dashboard', [
                'pendingPayments' => collect(),
                'recentPayments' => collect(),
                'stats' => [
                    'pending_payments' => 0,
                    'pending_total_vat_inclusive' => 0,
                    'pending_total_base' => 0,
                    'pending_total_vat' => 0,
                    'total_payments_this_month' => 0,
                    'total_amount_this_month' => 0,
                    'total_vat_this_month' => 0,
                    'base_amount_this_month' => 0,
                    'overdue_payments' => 0,
                    'total_expenses' => 0,
                ],
                'projectSpending' => collect(),
                'projects' => collect(),
            ]);
        }
    }
}