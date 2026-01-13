<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProjectsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Project::with(['users'])
            ->get()
            ->map(function ($project) {
                $projectManager = $project->users->where('role', 'project_manager')->first();
                $engineersCount = $project->users->where('role', 'engineer')->count();

                return [
                    'code' => $project->code,
                    'name' => $project->name,
                    'location' => $project->location,
                    'client' => $project->client_name ?? 'N/A',
                    'project_manager' => $projectManager ? $projectManager->name : 'Not Assigned',
                    'engineers' => $engineersCount,
                    'budget' => number_format($project->budget, 2),
                    'status' => ucfirst($project->status),
                    'start_date' => $project->start_date ? date('Y-m-d', strtotime($project->start_date)) : 'N/A',
                    'end_date' => $project->end_date ? date('Y-m-d', strtotime($project->end_date)) : 'N/A',
                    'description' => $project->description ?? '',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Project Name',
            'Location',
            'Client',
            'Project Manager',
            'Engineers',
            'Budget (UGX)',
            'Status',
            'Start Date',
            'End Date',
            'Description',
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
            'A' => 12,
            'B' => 30,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 10,
            'G' => 18,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 40,
        ];
    }

    public function title(): string
    {
        return 'Projects';
    }
}
