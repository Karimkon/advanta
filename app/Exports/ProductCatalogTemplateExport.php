<?php
// app/Exports/ProductCatalogTemplateExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProductCatalogTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        // Return sample data rows
        return collect([
            [
                'Cement 50kg',
                'Portland cement 50kg bags',
                'Construction Materials',
                'bags',
                'CEMENT-50KC',
                'Grade: 42.5N',
                '1'
            ],
            [
                'Steel Bars 12mm',
                'High tensile steel reinforcement bars',
                'Construction Materials',
                'pcs',
                'STEEL-12MM',
                'Grade 500',
                '1'
            ],
            [
                'Paint White 20L',
                'Exterior wall paint',
                'Painting Supplies',
                'tins',
                'PAINT-WT-20',
                'Weather resistant',
                '1'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'name*',
            'description',
            'category*',
            'unit*',
            'sku',
            'specifications',
            'is_active'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
            ],
            // Style sample data rows
            2 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]],
            3 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]],
            4 => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // name
            'B' => 35, // description
            'C' => 25, // category
            'D' => 15, // unit
            'E' => 18, // sku
            'F' => 30, // specifications
            'G' => 12, // is_active
        ];
    }
}