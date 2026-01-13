<?php

namespace App\Exports;

use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EquipmentExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Equipment::with(['project'])
            ->get()
            ->map(function ($equipment) {
                return [
                    'name' => $equipment->name,
                    'type' => ucfirst($equipment->type ?? 'N/A'),
                    'serial_number' => $equipment->serial_number ?? 'N/A',
                    'project' => $equipment->project->name ?? 'Unassigned',
                    'status' => ucfirst($equipment->status ?? 'unknown'),
                    'condition' => ucfirst($equipment->condition ?? 'N/A'),
                    'purchase_date' => $equipment->purchase_date ? date('Y-m-d', strtotime($equipment->purchase_date)) : 'N/A',
                    'purchase_cost' => number_format($equipment->purchase_cost ?? 0, 2),
                    'current_value' => number_format($equipment->current_value ?? 0, 2),
                    'last_maintenance' => $equipment->last_maintenance_date ? date('Y-m-d', strtotime($equipment->last_maintenance_date)) : 'N/A',
                    'notes' => $equipment->notes ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Type',
            'Serial Number',
            'Assigned Project',
            'Status',
            'Condition',
            'Purchase Date',
            'Purchase Cost (UGX)',
            'Current Value (UGX)',
            'Last Maintenance',
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
            'A' => 25,
            'B' => 15,
            'C' => 18,
            'D' => 25,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 18,
            'I' => 18,
            'J' => 15,
            'K' => 30,
        ];
    }

    public function title(): string
    {
        return 'Equipment';
    }
}
