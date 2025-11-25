<?php
// app/Http/Controllers/Finance/BulkLaborPaymentController.php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\LaborWorker;
use App\Models\LaborPayment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaborPaymentTemplateExport;
use App\Imports\BulkLaborPaymentImport;
use Carbon\Carbon;

class BulkLaborPaymentController extends Controller
{
    public function index()
    {
        $projects = Project::where('status', 'active')->get();
        return view('finance.labor.bulk-payments.index', compact('projects'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'payment_month' => 'required|date_format:Y-m'
        ]);

        $project = Project::findOrFail($request->project_id);
        $paymentDate = Carbon::createFromFormat('Y-m', $request->payment_month);
        
        // Get active workers for this project
        $workers = LaborWorker::where('project_id', $request->project_id)
            ->where('status', 'active')
            ->with(['payments' => function($query) use ($paymentDate) {
                $query->whereYear('payment_date', $paymentDate->year)
                      ->whereMonth('payment_date', $paymentDate->month);
            }])
            ->get();

        return view('finance.labor.bulk-payments.create', compact('project', 'workers', 'paymentDate'));
    }

    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'payment_month' => 'required|date_format:Y-m'
        ]);

        $project = Project::findOrFail($request->project_id);
        $paymentDate = Carbon::createFromFormat('Y-m', $request->payment_month);

        return Excel::download(new LaborPaymentTemplateExport($project->id, $paymentDate), 
            "labor_payments_{$project->name}_{$paymentDate->format('F_Y')}.xlsx");
    }

    public function processBulkImport(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'payment_month' => 'required|date_format:Y-m',
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money'
        ]);

        try {
            $import = new BulkLaborPaymentImport(
                $request->project_id,
                $request->payment_date,
                $request->payment_method,
                $request->payment_month
            );

            Excel::import($import, $request->file('import_file'));

            $results = $import->getResults();

            if (!empty($results['errors'])) {
                return back()->with([
                    'warning' => "Processed {$results['success_count']} payments, but some errors occurred.",
                    'import_errors' => $results['errors']
                ]);
            }

            return redirect()->route('finance.labor.index')
                ->with('success', "Successfully processed {$results['success_count']} labor payments!");

        } catch (\Exception $e) {
            return back()->with('error', 'Error processing bulk payments: ' . $e->getMessage());
        }
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money',
            'payments' => 'required|array',
            'payments.*.worker_id' => 'required|exists:labor_workers,id',
            'payments.*.days_worked' => 'required|integer|min:0',
            'payments.*.gross_amount' => 'required|numeric|min:0',
            'payments.*.nssf_amount' => 'required|numeric|min:0',
            'payments.*.description' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $successCount = 0;
            $errors = [];

            foreach ($request->payments as $paymentData) {
                if ($paymentData['days_worked'] == 0 && $paymentData['gross_amount'] == 0) {
                    continue; // Skip workers with no days worked and no amount
                }

                $worker = LaborWorker::find($paymentData['worker_id']);
                
                // Calculate period based on payment month
                $paymentMonth = Carbon::parse($request->payment_date);
                $periodStart = $paymentMonth->copy()->startOfMonth();
                $periodEnd = $paymentMonth->copy()->endOfMonth();

                // Calculate net amount
                $netAmount = $paymentData['gross_amount'] - $paymentData['nssf_amount'];

                if ($netAmount < 0) {
                    $errors[] = "Net amount cannot be negative for {$worker->name}";
                    continue;
                }

                // Create payment record
                $payment = LaborPayment::create([
                    'labor_worker_id' => $worker->id,
                    'payment_reference' => 'BULK-PAY-' . date('Ymd') . '-' . rand(1000, 9999),
                    'payment_date' => $request->payment_date,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'gross_amount' => $paymentData['gross_amount'],
                    'nssf_amount' => $paymentData['nssf_amount'],
                    'amount' => $netAmount,
                    'net_amount' => $netAmount,
                    'days_worked' => $paymentData['days_worked'],
                    'description' => $paymentData['description'],
                    'paid_by' => auth()->id(),
                    'payment_method' => $request->payment_method,
                    'notes' => $paymentData['notes'] ?? 'Bulk payment for ' . $paymentMonth->format('F Y'),
                ]);

                // Create expense record
                Expense::create([
                    'project_id' => $worker->project_id,
                    'type' => 'labor',
                    'description' => 'Bulk Labor Payment: ' . $paymentData['description'] . ' - ' . $worker->name . ' (' . $worker->role . ')',
                    'amount' => $netAmount,
                    'incurred_on' => $request->payment_date,
                    'recorded_by' => auth()->id(),
                    'status' => 'paid',
                    'notes' => $paymentData['notes'] ?? "Bulk payment | Period: " . $periodStart->format('M d') . " to " . $periodEnd->format('M d, Y') . " | Payment Ref: " . $payment->payment_reference . " | NSSF: UGX " . number_format($paymentData['nssf_amount'], 2),
                    'reference_id' => $payment->id,
                    'reference_type' => LaborPayment::class,
                ]);

                $successCount++;
            }

            DB::commit();

            if (!empty($errors)) {
                return back()->with([
                    'warning' => "Processed {$successCount} payments, but some errors occurred.",
                    'import_errors' => $errors
                ]);
            }

            return redirect()->route('finance.labor.index')
                ->with('success', "Successfully processed {$successCount} bulk labor payments!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing bulk payments: ' . $e->getMessage());
        }
    }

    public function getMonthlyReport(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'report_month' => 'required|date_format:Y-m'
        ]);

        $project = Project::findOrFail($request->project_id);
        $reportDate = Carbon::createFromFormat('Y-m', $request->report_month);

        $payments = LaborPayment::with(['laborWorker'])
            ->whereHas('laborWorker', function($query) use ($request) {
                $query->where('project_id', $request->project_id);
            })
            ->whereYear('payment_date', $reportDate->year)
            ->whereMonth('payment_date', $reportDate->month)
            ->get()
            ->groupBy('labor_worker_id');

        return view('finance.labor.bulk-payments.report', compact('project', 'payments', 'reportDate'));
    }
}