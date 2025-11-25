<?php
// app/Imports/BulkLaborPaymentImport.php

namespace App\Imports;

use App\Models\LaborWorker;
use App\Models\LaborPayment;
use App\Models\Expense;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BulkLaborPaymentImport implements ToCollection, WithHeadingRow
{
    protected $projectId;
    protected $paymentDate;
    protected $paymentMethod;
    protected $paymentMonth;
    
    protected $results = [
        'success_count' => 0,
        'errors' => []
    ];

    public function __construct($projectId, $paymentDate, $paymentMethod, $paymentMonth)
    {
        $this->projectId = $projectId;
        $this->paymentDate = $paymentDate;
        $this->paymentMethod = $paymentMethod;
        $this->paymentMonth = $paymentMonth;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                try {
                    // Skip rows with no worker ID or no amount
                    if (empty($row['worker_id']) || (empty($row['gross_amount_ugx']) && empty($row['days_worked_this_month']))) {
                        continue;
                    }

                    $worker = LaborWorker::where('id', $row['worker_id'])
                        ->where('project_id', $this->projectId)
                        ->first();

                    if (!$worker) {
                        $this->results['errors'][] = "Worker ID {$row['worker_id']} not found in project";
                        continue;
                    }

                    $daysWorked = $row['days_worked_this_month'] ?? 0;
                    $grossAmount = $row['gross_amount_ugx'] ?? 0;
                    $nssfAmount = $row['nssf_amount_ugx'] ?? ($grossAmount * 0.10); // Default 10%
                    $netAmount = $grossAmount - $nssfAmount;

                    if ($netAmount < 0) {
                        $this->results['errors'][] = "Net amount negative for {$worker->name}";
                        continue;
                    }

                    // Calculate period
                    $paymentMonth = Carbon::createFromFormat('Y-m', $this->paymentMonth);
                    $periodStart = $paymentMonth->copy()->startOfMonth();
                    $periodEnd = $paymentMonth->copy()->endOfMonth();

                    // Create payment record
                    $payment = LaborPayment::create([
                        'labor_worker_id' => $worker->id,
                        'payment_reference' => 'BULK-IMP-' . date('Ymd') . '-' . rand(1000, 9999),
                        'payment_date' => $this->paymentDate,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'gross_amount' => $grossAmount,
                        'nssf_amount' => $nssfAmount,
                        'amount' => $netAmount,
                        'net_amount' => $netAmount,
                        'days_worked' => $daysWorked,
                        'description' => $row['payment_description'] ?? 'Bulk imported payment for ' . $paymentMonth->format('F Y'),
                        'paid_by' => auth()->id(),
                        'payment_method' => $this->paymentMethod,
                        'notes' => $row['notes'] ?? 'Bulk imported payment',
                    ]);

                    // Create expense record
                    Expense::create([
                        'project_id' => $worker->project_id,
                        'type' => 'labor',
                        'description' => 'Bulk Import Labor: ' . ($row['payment_description'] ?? 'Monthly wages') . ' - ' . $worker->name,
                        'amount' => $netAmount,
                        'incurred_on' => $this->paymentDate,
                        'recorded_by' => auth()->id(),
                        'status' => 'paid',
                        'notes' => "Bulk import | Period: " . $periodStart->format('M d') . " to " . $periodEnd->format('M d, Y'),
                        'reference_id' => $payment->id,
                        'reference_type' => LaborPayment::class,
                    ]);

                    $this->results['success_count']++;

                } catch (\Exception $e) {
                    $this->results['errors'][] = "Error processing {$row['worker_name']}: " . $e->getMessage();
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->results['errors'][] = "Transaction failed: " . $e->getMessage();
        }
    }

    public function getResults()
    {
        return $this->results;
    }
}