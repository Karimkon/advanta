@extends('exports.pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Executive Financial Overview</h3>
    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="border: none; padding: 5px; width: 25%;">
                <div style="background: #dbeafe; padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-size: 8px; color: #1e40af;">Total Projects</div>
                    <div style="font-size: 14px; font-weight: bold; color: #1e40af;">{{ $stats['total_projects'] ?? 0 }}</div>
                </div>
            </td>
            <td style="border: none; padding: 5px; width: 25%;">
                <div style="background: #dcfce7; padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-size: 8px; color: #166534;">Total Budget</div>
                    <div style="font-size: 12px; font-weight: bold; color: #166534;">UGX {{ number_format($stats['total_budget'] ?? 0, 0) }}</div>
                </div>
            </td>
            <td style="border: none; padding: 5px; width: 25%;">
                <div style="background: #fee2e2; padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-size: 8px; color: #991b1b;">Total Expenses</div>
                    <div style="font-size: 12px; font-weight: bold; color: #991b1b;">UGX {{ number_format($stats['total_expenses'] ?? 0, 0) }}</div>
                </div>
            </td>
            <td style="border: none; padding: 5px; width: 25%;">
                <div style="background: #fef3c7; padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-size: 8px; color: #92400e;">Total Payments</div>
                    <div style="font-size: 12px; font-weight: bold; color: #92400e;">UGX {{ number_format($stats['total_payments'] ?? 0, 0) }}</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<h3 style="font-size: 12px; color: #1e40af; margin: 15px 0 10px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Projects Financial Summary</h3>
<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Project Name</th>
            <th class="text-right">Budget (UGX)</th>
            <th class="text-right">Spent (UGX)</th>
            <th class="text-right">Remaining (UGX)</th>
            <th class="text-center">Used %</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projects as $project)
        @php
            $spent = $project->total_spent ?? 0;
            $budget = $project->budget ?? 0;
            $remaining = $budget - $spent;
            $usedPct = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0;
        @endphp
        <tr>
            <td>{{ $project->code }}</td>
            <td>{{ $project->name }}</td>
            <td class="amount">{{ number_format($budget, 2) }}</td>
            <td class="amount">{{ number_format($spent, 2) }}</td>
            <td class="amount">{{ number_format($remaining, 2) }}</td>
            <td class="text-center">
                <span class="badge badge-{{ $usedPct > 90 ? 'danger' : ($usedPct > 70 ? 'warning' : 'success') }}">
                    {{ $usedPct }}%
                </span>
            </td>
            <td class="text-center">
                <span class="badge badge-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'secondary') }}">
                    {{ ucfirst($project->status) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="2"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($projects->sum('budget'), 2) }}</td>
            <td class="amount">{{ number_format($projects->sum('total_spent'), 2) }}</td>
            <td class="amount">{{ number_format($projects->sum('budget') - $projects->sum('total_spent'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@if(isset($recentPayments) && $recentPayments->count() > 0)
<h3 style="font-size: 12px; color: #1e40af; margin: 15px 0 10px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Recent Payments</h3>
<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Project</th>
            <th>Supplier</th>
            <th class="text-right">Amount (UGX)</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($recentPayments->take(10) as $payment)
        <tr>
            <td>{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</td>
            <td>{{ $payment->lpo->requisition->project->name ?? 'N/A' }}</td>
            <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
            <td class="amount">{{ number_format($payment->amount, 2) }}</td>
            <td class="text-center">
                <span class="badge badge-{{ $payment->approval_status === 'ceo_approved' ? 'success' : 'warning' }}">
                    {{ ucfirst(str_replace('_', ' ', $payment->approval_status ?? '')) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
