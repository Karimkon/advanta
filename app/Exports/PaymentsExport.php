<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PaymentsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Payment::with(['lpo.requisition.project', 'supplier', 'paidBy'])
            ->whereNotNull('paid_on');

        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('paid_on', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('paid_on', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get()->map(function ($payment) {
            $baseAmount = $payment->amount - $payment->vat_amount;
            $vatPercentage = $baseAmount > 0 ? round(($payment->vat_amount / $baseAmount) * 100, 1) : 0;

            return [
                'id' => $payment->id,
                'date' => $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                'project' => $payment->lpo->requisition->project->name ?? 'N/A',
                'supplier' => $payment->supplier->name ?? 'N/A',
                'lpo_number' => $payment->lpo->lpo_number ?? 'N/A',
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? '')),
                'base_amount' => number_format($baseAmount, 2),
                'vat_percentage' => $vatPercentage . '%',
                'vat_amount' => number_format($payment->vat_amount, 2),
                'total_amount' => number_format($payment->amount, 2),
                'status' => ucfirst($payment->status ?? 'unknown'),
                'approval_status' => ucfirst(str_replace('_', ' ', $payment->approval_status ?? '')),
                'reference' => $payment->reference ?? 'N/A',
                'processed_by' => $payment->paidBy->name ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Payment ID',
            'Date',
            'Project',
            'Supplier',
            'LPO Number',
            'Payment Method',
            'Base Amount (UGX)',
            'VAT %',
            'VAT Amount (UGX)',
            'Total Amount (UGX)',
            'Status',
            'Approval Status',
            'Reference',
            'Processed By',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 12,
            'C' => 20,
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 8,
            'I' => 15,
            'J' => 15,
            'K' => 12,
            'L' => 15,
            'M' => 15,
            'N' => 18,
        ];
    }

    public function title(): string
    {
        return 'Payments';
    }
}
