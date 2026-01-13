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

class CEOPaymentsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $approvalStatus;

    public function __construct($approvalStatus = null)
    {
        $this->approvalStatus = $approvalStatus;
    }

    public function collection()
    {
        $query = Payment::with(['lpo.requisition.project', 'supplier', 'paidBy', 'approvedBy']);

        if ($this->approvalStatus) {
            $query->where('approval_status', $this->approvalStatus);
        }

        return $query->latest()->get()->map(function ($payment) {
            $baseAmount = $payment->amount - $payment->vat_amount;

            return [
                'id' => $payment->id,
                'date' => $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                'project' => $payment->lpo?->requisition?->project?->name ?? 'N/A',
                'supplier' => $payment->supplier?->name ?? 'N/A',
                'lpo_number' => $payment->lpo?->lpo_number ?? 'N/A',
                'base_amount' => number_format($baseAmount, 2),
                'vat_amount' => number_format($payment->vat_amount, 2),
                'total_amount' => number_format($payment->amount, 2),
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? '')),
                'approval_status' => ucfirst(str_replace('_', ' ', $payment->approval_status ?? '')),
                'approved_by' => $payment->approvedBy?->name ?? 'Pending',
                'processed_by' => $payment->paidBy?->name ?? 'N/A',
                'ceo_notes' => $payment->ceo_notes ?? '',
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
            'Base Amount (UGX)',
            'VAT Amount (UGX)',
            'Total Amount (UGX)',
            'Payment Method',
            'Approval Status',
            'Approved By',
            'Processed By',
            'CEO Notes',
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
                    'startColor' => ['rgb' => '059669']
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
            'D' => 18,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
            'M' => 25,
        ];
    }

    public function title(): string
    {
        return 'Payments Approval Report';
    }
}
