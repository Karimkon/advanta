@extends('exports.pdf.layout')

@section('content')
@php
    $totalAmount = $payments->sum('amount');
    $totalVat = $payments->sum('vat_amount');
    $totalBase = $totalAmount - $totalVat;
@endphp

<div class="summary-box">
    <h3>Summary</h3>
    <table style="width: 70%; border: none;">
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Payments:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $payments->count() }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Approved:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $payments->where('approval_status', 'ceo_approved')->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Base Amount:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($totalBase, 2) }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Pending:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $payments->where('approval_status', 'pending_ceo')->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total VAT:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($totalVat, 2) }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Amount:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($totalAmount, 2) }}</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Project</th>
            <th>Supplier</th>
            <th>Method</th>
            <th class="text-right">Base (UGX)</th>
            <th class="text-right">VAT (UGX)</th>
            <th class="text-right">Total (UGX)</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        @php
            $baseAmount = $payment->amount - $payment->vat_amount;
        @endphp
        <tr>
            <td>#{{ $payment->id }}</td>
            <td>{{ $payment->paid_on ? $payment->paid_on->format('M d, Y') : 'N/A' }}</td>
            <td>{{ $payment->lpo->requisition->project->name ?? 'N/A' }}</td>
            <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? '')) }}</td>
            <td class="amount">{{ number_format($baseAmount, 2) }}</td>
            <td class="amount">{{ number_format($payment->vat_amount, 2) }}</td>
            <td class="amount">{{ number_format($payment->amount, 2) }}</td>
            <td class="text-center">
                @php
                    $statusClass = 'secondary';
                    if ($payment->approval_status === 'ceo_approved') $statusClass = 'success';
                    elseif ($payment->approval_status === 'pending_ceo') $statusClass = 'warning';
                    elseif ($payment->approval_status === 'ceo_rejected') $statusClass = 'danger';
                @endphp
                <span class="badge badge-{{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $payment->approval_status ?? $payment->status ?? '')) }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($totalBase, 2) }}</td>
            <td class="amount">{{ number_format($totalVat, 2) }}</td>
            <td class="amount">{{ number_format($totalAmount, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
@endsection
