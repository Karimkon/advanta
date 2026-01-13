<?php

namespace App\Exports;

use App\Models\Requisition;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RequisitionsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Requisition::with(['project', 'requestedBy', 'items', 'supplier']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get()->map(function ($requisition) {
            return [
                'ref' => $requisition->ref,
                'project' => $requisition->project->name ?? 'N/A',
                'type' => ucfirst(str_replace('_', ' ', $requisition->type)),
                'requested_by' => $requisition->requestedBy->name ?? 'N/A',
                'supplier' => $requisition->supplier->name ?? 'N/A',
                'items_count' => $requisition->items->count(),
                'estimated_total' => number_format($requisition->estimated_total ?? 0, 2),
                'status' => ucfirst(str_replace('_', ' ', $requisition->status)),
                'priority' => ucfirst($requisition->priority ?? 'normal'),
                'date' => $requisition->created_at->format('Y-m-d'),
                'notes' => $requisition->notes ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Project',
            'Type',
            'Requested By',
            'Supplier',
            'Items',
            'Estimated Total (UGX)',
            'Status',
            'Priority',
            'Date',
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
            'A' => 15,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 8,
            'G' => 18,
            'H' => 18,
            'I' => 10,
            'J' => 12,
            'K' => 35,
        ];
    }

    public function title(): string
    {
        return 'Requisitions';
    }
}
