<?php

namespace App\Exports;

use App\Models\Subcontractor;
use App\Models\ProjectSubcontractor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SubcontractorPaymentsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return ProjectSubcontractor::with(['subcontractor', 'project', 'payments'])
            ->get()
            ->map(function ($contract) {
                $totalPaid = $contract->payments->sum('amount');
                $balance = ($contract->contract_amount ?? 0) - $totalPaid;

                return [
                    'subcontractor' => $contract->subcontractor->name ?? 'N/A',
                    'company' => $contract->subcontractor->company_name ?? 'N/A',
                    'project' => $contract->project->name ?? 'N/A',
                    'work_description' => $contract->work_description ?? 'N/A',
                    'contract_amount' => number_format($contract->contract_amount ?? 0, 2),
                    'payments_count' => $contract->payments->count(),
                    'total_paid' => number_format($totalPaid, 2),
                    'balance' => number_format($balance, 2),
                    'status' => ucfirst($contract->status ?? 'active'),
                    'start_date' => $contract->start_date ? date('Y-m-d', strtotime($contract->start_date)) : 'N/A',
                    'end_date' => $contract->end_date ? date('Y-m-d', strtotime($contract->end_date)) : 'N/A',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Subcontractor',
            'Company',
            'Project',
            'Work Description',
            'Contract Amount (UGX)',
            'Payments Made',
            'Total Paid (UGX)',
            'Balance (UGX)',
            'Status',
            'Start Date',
            'End Date',
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
            'B' => 20,
            'C' => 20,
            'D' => 25,
            'E' => 18,
            'F' => 12,
            'G' => 15,
            'H' => 15,
            'I' => 10,
            'J' => 12,
            'K' => 12,
        ];
    }

    public function title(): string
    {
        return 'Subcontractor Payments';
    }
}
