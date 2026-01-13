<?php

namespace App\Exports;

use App\Models\LaborWorker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaborWorkersExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $projectId;

    public function __construct($projectId = null)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        $query = LaborWorker::with(['project', 'payments']);

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        return $query->get()->map(function ($worker) {
            $totalPaid = $worker->payments->sum('amount');

            return [
                'name' => $worker->name,
                'phone' => $worker->phone ?? 'N/A',
                'id_number' => $worker->id_number ?? 'N/A',
                'project' => $worker->project->name ?? 'N/A',
                'job_type' => ucfirst($worker->job_type ?? 'General'),
                'daily_rate' => number_format($worker->daily_rate ?? 0, 2),
                'status' => ucfirst($worker->status ?? 'active'),
                'start_date' => $worker->start_date ? date('Y-m-d', strtotime($worker->start_date)) : 'N/A',
                'payments_count' => $worker->payments->count(),
                'total_paid' => number_format($totalPaid, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'ID Number',
            'Project',
            'Job Type',
            'Daily Rate (UGX)',
            'Status',
            'Start Date',
            'Payments Made',
            'Total Paid (UGX)',
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
            'C' => 15,
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 10,
            'H' => 12,
            'I' => 12,
            'J' => 15,
        ];
    }

    public function title(): string
    {
        return 'Labor Workers';
    }
}
