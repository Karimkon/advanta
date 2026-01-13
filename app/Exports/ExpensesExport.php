<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExpensesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Expense::with(['project', 'recordedBy']);

        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('incurred_on', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('incurred_on', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get()->map(function ($expense) {
            return [
                'id' => $expense->id,
                'date' => $expense->incurred_on ? date('Y-m-d', strtotime($expense->incurred_on)) : 'N/A',
                'project' => $expense->project->name ?? 'General',
                'type' => ucfirst(str_replace('_', ' ', $expense->type ?? '')),
                'description' => $expense->description ?? '',
                'amount' => number_format($expense->amount, 2),
                'status' => ucfirst($expense->status ?? 'pending'),
                'recorded_by' => $expense->recordedBy->name ?? 'N/A',
                'notes' => $expense->notes ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Project',
            'Type',
            'Description',
            'Amount (UGX)',
            'Status',
            'Recorded By',
            'Notes',
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
            'A' => 8,
            'B' => 12,
            'C' => 20,
            'D' => 18,
            'E' => 35,
            'F' => 15,
            'G' => 12,
            'H' => 18,
            'I' => 30,
        ];
    }

    public function title(): string
    {
        return 'Expenses';
    }
}
