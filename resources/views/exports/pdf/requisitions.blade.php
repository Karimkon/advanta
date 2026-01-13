@extends('exports.pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Summary</h3>
    <table style="width: 60%; border: none;">
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Requisitions:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $requisitions->count() }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Pending:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $requisitions->where('status', 'pending')->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Value:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($requisitions->sum('estimated_total'), 2) }}</strong></td>
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Approved:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $requisitions->whereIn('status', ['operations_approved', 'ceo_approved', 'lpo_issued'])->count() }}</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th>Reference</th>
            <th>Project</th>
            <th>Type</th>
            <th>Requested By</th>
            <th>Supplier</th>
            <th class="text-right">Est. Total (UGX)</th>
            <th class="text-center">Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requisitions as $requisition)
        <tr>
            <td>{{ $requisition->ref }}</td>
            <td>{{ $requisition->project->name ?? 'N/A' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $requisition->type)) }}</td>
            <td>{{ $requisition->requestedBy->name ?? 'N/A' }}</td>
            <td>{{ $requisition->supplier->name ?? 'N/A' }}</td>
            <td class="amount">{{ number_format($requisition->estimated_total ?? 0, 2) }}</td>
            <td class="text-center">
                @php
                    $statusClass = 'secondary';
                    if (in_array($requisition->status, ['pending'])) $statusClass = 'warning';
                    elseif (in_array($requisition->status, ['operations_approved', 'ceo_approved'])) $statusClass = 'success';
                    elseif ($requisition->status === 'rejected') $statusClass = 'danger';
                    elseif (in_array($requisition->status, ['lpo_issued', 'delivered'])) $statusClass = 'info';
                @endphp
                <span class="badge badge-{{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $requisition->status)) }}
                </span>
            </td>
            <td>{{ $requisition->created_at->format('M d, Y') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($requisitions->sum('estimated_total'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@endsection
