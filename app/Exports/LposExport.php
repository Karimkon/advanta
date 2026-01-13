<?php

namespace App\Exports;

use App\Models\Lpo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LposExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Lpo::with(['supplier', 'requisition.project', 'items', 'issuer']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['supplier_id'])) {
            $query->where('supplier_id', $this->filters['supplier_id']);
        }

        return $query->latest()->get()->map(function ($lpo) {
            return [
                'lpo_number' => $lpo->lpo_number,
                'project' => $lpo->requisition?->project?->name ?? 'N/A',
                'supplier' => $lpo->supplier?->name ?? 'N/A',
                'items_count' => $lpo->items->count(),
                'subtotal' => number_format($lpo->subtotal ?? 0, 2),
                'vat_amount' => number_format($lpo->vat_amount ?? 0, 2),
                'total' => number_format($lpo->total ?? 0, 2),
                'status' => ucfirst(str_replace('_', ' ', $lpo->status)),
                'issued_by' => $lpo->issuer?->name ?? 'N/A',
                'issued_date' => $lpo->created_at->format('Y-m-d'),
                'delivery_date' => $lpo->delivery_date ? date('Y-m-d', strtotime($lpo->delivery_date)) : 'Pending',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'LPO Number',
            'Project',
            'Supplier',
            'Items',
            'Subtotal (UGX)',
            'VAT (UGX)',
            'Total (UGX)',
            'Status',
            'Issued By',
            'Issued Date',
            'Delivery Date',
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
            'C' => 25,
            'D' => 8,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 12,
            'I' => 18,
            'J' => 12,
            'K' => 12,
        ];
    }

    public function title(): string
    {
        return 'LPOs';
    }
}
