@extends('exports.pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Summary</h3>
    <table style="width: 60%; border: none;">
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total LPOs:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $lpos->count() }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Delivered:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $lpos->where('status', 'delivered')->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Value:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($lpos->sum('total'), 2) }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Pending:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $lpos->whereIn('status', ['issued', 'sent', 'pending'])->count() }}</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th>LPO Number</th>
            <th>Project</th>
            <th>Supplier</th>
            <th class="text-right">Subtotal (UGX)</th>
            <th class="text-right">VAT (UGX)</th>
            <th class="text-right">Total (UGX)</th>
            <th class="text-center">Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lpos as $lpo)
        <tr>
            <td>{{ $lpo->lpo_number }}</td>
            <td>{{ $lpo->requisition->project->name ?? 'N/A' }}</td>
            <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
            <td class="amount">{{ number_format($lpo->subtotal ?? 0, 2) }}</td>
            <td class="amount">{{ number_format($lpo->vat_amount ?? 0, 2) }}</td>
            <td class="amount">{{ number_format($lpo->total ?? 0, 2) }}</td>
            <td class="text-center">
                @php
                    $statusClass = 'secondary';
                    if (in_array($lpo->status, ['issued', 'sent', 'pending'])) $statusClass = 'warning';
                    elseif ($lpo->status === 'delivered') $statusClass = 'success';
                    elseif ($lpo->status === 'cancelled') $statusClass = 'danger';
                @endphp
                <span class="badge badge-{{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $lpo->status)) }}
                </span>
            </td>
            <td>{{ $lpo->created_at->format('M d, Y') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($lpos->sum('subtotal'), 2) }}</td>
            <td class="amount">{{ number_format($lpos->sum('vat_amount'), 2) }}</td>
            <td class="amount">{{ number_format($lpos->sum('total'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@endsection
