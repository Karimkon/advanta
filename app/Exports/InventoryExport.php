<?php

namespace App\Exports;

use App\Models\InventoryItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InventoryExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $storeId;

    public function __construct($storeId = null)
    {
        $this->storeId = $storeId;
    }

    public function collection()
    {
        $query = InventoryItem::with(['store.project', 'productCatalog']);

        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        return $query->orderBy('store_id')->orderBy('name')->get()->map(function ($item) {
            $totalValue = $item->quantity * $item->unit_price;
            $stockStatus = $item->quantity <= 0 ? 'Out of Stock' :
                          ($item->quantity < $item->reorder_level ? 'Low Stock' : 'In Stock');

            return [
                'store' => $item->store->display_name ?? 'N/A',
                'project' => $item->store->project->name ?? 'Main Store',
                'sku' => $item->sku ?? 'N/A',
                'name' => $item->name,
                'category' => $item->category ?? 'General',
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => number_format($item->unit_price, 2),
                'total_value' => number_format($totalValue, 2),
                'reorder_level' => $item->reorder_level,
                'stock_status' => $stockStatus,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Store',
            'Project',
            'SKU',
            'Item Name',
            'Category',
            'Quantity',
            'Unit',
            'Unit Price (UGX)',
            'Total Value (UGX)',
            'Reorder Level',
            'Stock Status',
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
            'C' => 15,
            'D' => 30,
            'E' => 15,
            'F' => 10,
            'G' => 10,
            'H' => 15,
            'I' => 15,
            'J' => 12,
            'K' => 12,
        ];
    }

    public function title(): string
    {
        return 'Inventory';
    }
}
