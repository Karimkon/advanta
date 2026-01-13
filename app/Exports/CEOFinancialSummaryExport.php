<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Requisition;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CEOFinancialSummaryExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProjectsSummarySheet(),
            new PaymentsSummarySheet(),
            new ExpensesSummarySheet(),
        ];
    }
}

class ProjectsSummarySheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        return Project::with(['expenses', 'requisitions.lpo.payments'])
            ->get()
            ->map(function ($project) {
                $totalExpenses = $project->expenses->sum('amount');
                $totalPayments = $project->requisitions->flatMap(function ($req) {
                    return $req->lpo ? $req->lpo->payments : collect();
                })->sum('amount');

                $totalSpent = $totalExpenses + $totalPayments;
                $budget = $project->budget ?? 0;
                $remaining = $budget - $totalSpent;
                $usedPercentage = $budget > 0 ? round(($totalSpent / $budget) * 100, 1) : 0;

                return [
                    $project->code,
                    $project->name,
                    number_format($budget, 2),
                    number_format($totalSpent, 2),
                    number_format($remaining, 2),
                    $usedPercentage . '%',
                    ucfirst($project->status),
                ];
            })->toArray();
    }

    public function headings(): array
    {
        return [
            'Code',
            'Project Name',
            'Budget (UGX)',
            'Total Spent (UGX)',
            'Remaining (UGX)',
            'Used %',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 30, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 10, 'G' => 12];
    }

    public function title(): string
    {
        return 'Projects Summary';
    }
}

class PaymentsSummarySheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        $payments = Payment::with(['lpo.requisition.project', 'supplier'])
            ->whereNotNull('paid_on')
            ->latest()
            ->take(100)
            ->get();

        return $payments->map(function ($payment) {
            return [
                $payment->id,
                $payment->paid_on ? $payment->paid_on->format('Y-m-d') : 'N/A',
                $payment->lpo->requisition->project->name ?? 'N/A',
                $payment->supplier->name ?? 'N/A',
                number_format($payment->amount - $payment->vat_amount, 2),
                number_format($payment->vat_amount, 2),
                number_format($payment->amount, 2),
                ucfirst(str_replace('_', ' ', $payment->approval_status ?? '')),
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Project',
            'Supplier',
            'Base Amount (UGX)',
            'VAT (UGX)',
            'Total (UGX)',
            'Approval Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 12, 'C' => 25, 'D' => 20, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15];
    }

    public function title(): string
    {
        return 'Recent Payments';
    }
}

class ExpensesSummarySheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        return Expense::with(['project'])
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get()
            ->map(function ($expense) {
                return [
                    ucfirst(str_replace('_', ' ', $expense->type ?? 'Other')),
                    $expense->count,
                    number_format($expense->total, 2),
                ];
            })->toArray();
    }

    public function headings(): array
    {
        return [
            'Expense Type',
            'Count',
            'Total Amount (UGX)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 10, 'C' => 20];
    }

    public function title(): string
    {
        return 'Expenses by Type';
    }
}
