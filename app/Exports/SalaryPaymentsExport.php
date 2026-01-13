<?php

namespace App\Exports;

use App\Models\SalaryPayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SalaryPaymentsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SalaryPayment::with(['officeStaff', 'paidBy']);

        if (!empty($this->filters['month'])) {
            $query->where('month', $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->where('year', $this->filters['year']);
        }

        return $query->latest()->get()->map(function ($payment) {
            return [
                'staff_name' => $payment->officeStaff->name ?? 'N/A',
                'position' => $payment->officeStaff->position ?? 'N/A',
                'department' => $payment->officeStaff->department ?? 'N/A',
                'month' => $payment->month ?? 'N/A',
                'year' => $payment->year ?? 'N/A',
                'basic_salary' => number_format($payment->basic_salary ?? 0, 2),
                'allowances' => number_format($payment->allowances ?? 0, 2),
                'deductions' => number_format($payment->deductions ?? 0, 2),
                'net_salary' => number_format($payment->net_salary ?? 0, 2),
                'payment_date' => $payment->payment_date ? date('Y-m-d', strtotime($payment->payment_date)) : 'N/A',
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? '')),
                'paid_by' => $payment->paidBy->name ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Staff Name',
            'Position',
            'Department',
            'Month',
            'Year',
            'Basic Salary (UGX)',
            'Allowances (UGX)',
            'Deductions (UGX)',
            'Net Salary (UGX)',
            'Payment Date',
            'Payment Method',
            'Paid By',
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
            'A' => 20,
            'B' => 18,
            'C' => 15,
            'D' => 12,
            'E' => 8,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 12,
            'K' => 15,
            'L' => 18,
        ];
    }

    public function title(): string
    {
        return 'Salary Payments';
    }
}
