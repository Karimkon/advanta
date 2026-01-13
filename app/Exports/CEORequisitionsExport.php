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

class CEORequisitionsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $status;

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    public function collection()
    {
        $query = Requisition::with(['project', 'requestedBy', 'items', 'supplier', 'lpo']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get()->map(function ($requisition) {
            $lpoTotal = $requisition->lpo ? $requisition->lpo->total : 0;

            return [
                'ref' => $requisition->ref,
                'project' => $requisition->project->name ?? 'N/A',
                'type' => ucfirst(str_replace('_', ' ', $requisition->type)),
                'requested_by' => $requisition->requestedBy->name ?? 'N/A',
                'supplier' => $requisition->supplier->name ?? 'N/A',
                'items_count' => $requisition->items->count(),
                'estimated_total' => number_format($requisition->estimated_total ?? 0, 2),
                'lpo_total' => number_format($lpoTotal, 2),
                'status' => ucfirst(str_replace('_', ' ', $requisition->status)),
                'priority' => ucfirst($requisition->priority ?? 'normal'),
                'date' => $requisition->created_at->format('Y-m-d'),
                'has_lpo' => $requisition->lpo ? 'Yes' : 'No',
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
            'LPO Total (UGX)',
            'Status',
            'Priority',
            'Date',
            'Has LPO',
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
                    'startColor' => ['rgb' => '7C3AED']
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
            'B' => 22,
            'C' => 15,
            'D' => 18,
            'E' => 18,
            'F' => 8,
            'G' => 18,
            'H' => 15,
            'I' => 18,
            'J' => 10,
            'K' => 12,
            'L' => 10,
        ];
    }

    public function title(): string
    {
        return 'Requisitions Overview';
    }
}
