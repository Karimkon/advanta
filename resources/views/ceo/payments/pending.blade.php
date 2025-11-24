<!-- resources/views/ceo/payments/pending.blade.php -->
@extends('ceo.layouts.app')

@section('title', 'Pending Payment Approvals')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Pending Payment Approvals</h2>
            <p class="text-muted mb-0">Approve or reject supplier payments</p>
        </div>
        <span class="badge bg-warning fs-6">Pending: {{ $pendingPayments->total() }}</span>
    </div>

    @if($pendingPayments->isEmpty())
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> No pending payments for approval.
        </div>
    @else
        <div class="row">
            @foreach($pendingPayments as $payment)
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Payment: {{ $payment->reference }}</h5>
                                <span class="badge bg-warning">Pending CEO Approval</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Supplier:</strong> {{ $payment->supplier->name }}</p>
                                    <p><strong>Amount:</strong> UGX {{ number_format($payment->amount, 2) }}</p>
                                    <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project:</strong> {{ $payment->lpo->requisition->project->name }}</p>
                                    <p><strong>LPO:</strong> {{ $payment->lpo->lpo_number }}</p>
                                    <p><strong>Created:</strong> {{ $payment->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            
                            @if($payment->notes)
                                <div class="alert alert-info mt-2">
                                    <strong>Notes:</strong> {{ $payment->notes }}
                                </div>
                            @endif

                            <div class="mt-3">
                                <a href="{{ route('ceo.payments.show', $payment) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> Review Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $pendingPayments->links() }}
        </div>
    @endif
</div>
@endsection