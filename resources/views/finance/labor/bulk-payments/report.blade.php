@extends('finance.layouts.app')

@section('title', 'Labor Payments Report - ' . $project->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Labor Payments Report</h2>
            <p class="text-muted mb-0">{{ $project->name }} - {{ $reportDate->format('F Y') }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.labor.bulk-payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Monthly Payments Summary</h5>
        </div>
        <div class="card-body">
            @if($payments->count() > 0)
                @php
                    $totalGross = 0;
                    $totalNssf = 0;
                    $totalNet = 0;
                    $workerCount = 0;
                @endphp

                @foreach($payments as $workerId => $workerPayments)
                    @php
                        $worker = $workerPayments->first()->laborWorker;
                        $workerGross = $workerPayments->sum('gross_amount');
                        $workerNssf = $workerPayments->sum('nssf_amount');
                        $workerNet = $workerPayments->sum('amount');
                        
                        $totalGross += $workerGross;
                        $totalNssf += $workerNssf;
                        $totalNet += $workerNet;
                        $workerCount++;
                    @endphp

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">{{ $worker->name }} - {{ $worker->role }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Payment Date</th>
                                            <th>Description</th>
                                            <th>Days Worked</th>
                                            <th>Gross Amount</th>
                                            <th>NSSF</th>
                                            <th>Net Amount</th>
                                            <th>Method</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workerPayments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                                <td>{{ $payment->description }}</td>
                                                <td class="text-center">{{ $payment->days_worked }}</td>
                                                <td class="text-end">UGX {{ number_format($payment->gross_amount, 2) }}</td>
                                                <td class="text-end">UGX {{ number_format($payment->nssf_amount, 2) }}</td>
                                                <td class="text-end text-success fw-bold">UGX {{ number_format($payment->amount, 2) }}</td>
                                                <td class="text-capitalize">{{ $payment->payment_method }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-warning">
                                            <td colspan="3" class="text-end fw-bold">Worker Total:</td>
                                            <td class="text-end fw-bold">UGX {{ number_format($workerGross, 2) }}</td>
                                            <td class="text-end fw-bold">UGX {{ number_format($workerNssf, 2) }}</td>
                                            <td class="text-end fw-bold text-success">UGX {{ number_format($workerNet, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Summary -->
                <div class="alert alert-success">
                    <h6>Monthly Summary</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Total Workers:</strong> {{ $workerCount }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Gross:</strong> UGX {{ number_format($totalGross, 2) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total NSSF:</strong> UGX {{ number_format($totalNssf, 2) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Net Paid:</strong> UGX {{ number_format($totalNet, 2) }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-cash-coin display-4 d-block mb-2"></i>
                        No payments found for {{ $reportDate->format('F Y') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .card-header h5 {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>
@endsection