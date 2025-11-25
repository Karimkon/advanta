<?php
// app/Exports/LaborPaymentTemplateExport.php

namespace App\Exports;

use App\Models\LaborWorker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class LaborPaymentTemplateExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $projectId;
    protected $paymentDate;

    public function __construct($projectId, Carbon $paymentDate)
    {
        $this->projectId = $projectId;
        $this->paymentDate = $paymentDate;
    }

    public function collection()
    {
        return LaborWorker::where('project_id', $this->projectId)
            ->where('status', 'active')
            ->with(['payments' => function($query) {
                $query->whereYear('payment_date', $this->paymentDate->year)
                      ->whereMonth('payment_date', $this->paymentDate->month);
            }])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Worker ID',
            'Worker Name',
            'Role',
            'Payment Frequency',
            'Standard Rate (UGX)',
            'Days Worked This Month',
            'Gross Amount (UGX)',
            'NSSF Amount (UGX)',
            'Net Amount (UGX)',
            'Payment Description',
            'Notes'
        ];
    }

    public function map($worker): array
    {
        $existingPayment = $worker->payments->first();
        
        return [
            $worker->id,
            $worker->name,
            $worker->role,
            $worker->payment_frequency,
            $worker->current_rate,
            $existingPayment ? $existingPayment->days_worked : 0,
            $existingPayment ? $existingPayment->gross_amount : 0,
            $existingPayment ? $existingPayment->nssf_amount : 0,
            $existingPayment ? $existingPayment->amount : 0,
            $existingPayment ? $existingPayment->description : 'Monthly wages for ' . $this->paymentDate->format('F Y'),
            $existingPayment ? $existingPayment->notes : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:K' => ['alignment' => ['wrapText' => true]],
        ];
    }
}