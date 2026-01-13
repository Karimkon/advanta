@extends('exports.pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Summary</h3>
    <table style="width: 60%; border: none;">
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Expenses:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $expenses->count() }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Approved:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $expenses->where('status', 'approved')->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Amount:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($expenses->sum('amount'), 2) }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Pending:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $expenses->where('status', 'pending')->count() }}</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Project</th>
            <th>Type</th>
            <th>Description</th>
            <th class="text-right">Amount (UGX)</th>
            <th class="text-center">Status</th>
            <th>Recorded By</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $expense)
        <tr>
            <td>#{{ $expense->id }}</td>
            <td>{{ $expense->incurred_on ? date('M d, Y', strtotime($expense->incurred_on)) : 'N/A' }}</td>
            <td>{{ $expense->project?->name ?? 'General' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $expense->type ?? '')) }}</td>
            <td>{{ Str::limit($expense->description, 40) }}</td>
            <td class="amount">{{ number_format($expense->amount, 2) }}</td>
            <td class="text-center">
                @php
                    $statusClass = 'secondary';
                    if ($expense->status === 'approved') $statusClass = 'success';
                    elseif ($expense->status === 'pending') $statusClass = 'warning';
                    elseif ($expense->status === 'rejected') $statusClass = 'danger';
                @endphp
                <span class="badge badge-{{ $statusClass }}">
                    {{ ucfirst($expense->status ?? 'pending') }}
                </span>
            </td>
            <td>{{ $expense->recordedBy?->name ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($expenses->sum('amount'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@endsection
