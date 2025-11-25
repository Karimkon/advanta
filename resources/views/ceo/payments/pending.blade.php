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
                                <h5 class="mb-0">Payment: PAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h5>
                                <span class="badge bg-warning">Pending CEO Approval</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Supplier:</strong> {{ $payment->supplier->name ?? 'N/A' }}</p>
                                    <p><strong>Amount:</strong> UGX {{ number_format($payment->amount, 2) }}</p>
                                    <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Project:</strong> {{ $payment->lpo->requisition->project->name ?? 'N/A' }}</p>
                                    <p><strong>LPO:</strong> {{ $payment->lpo->lpo_number ?? 'N/A' }}</p>
                                    <p><strong>Created:</strong> {{ $payment->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            
                            <!-- Payment Breakdown -->
                            <div class="alert alert-light mt-2">
                                <h6 class="mb-2">Payment Breakdown:</h6>
                                <div class="row small">
                                    <div class="col-4">
                                        <strong>Subtotal:</strong><br>
                                        UGX {{ number_format($payment->amount - $payment->vat_amount - $payment->additional_costs, 2) }}
                                    </div>
                                    @if($payment->vat_amount > 0)
                                    <div class="col-4">
                                        <strong>VAT:</strong><br>
                                        UGX {{ number_format($payment->vat_amount, 2) }}
                                    </div>
                                    @endif
                                    @if($payment->additional_costs > 0)
                                    <div class="col-4">
                                        <strong>{{ $payment->additional_costs_description ?: 'Additional' }}:</strong><br>
                                        UGX {{ number_format($payment->additional_costs, 2) }}
                                    </div>
                                    @endif
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
                                <button type="button" class="btn btn-success btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveModal{{ $payment->id }}">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal{{ $payment->id }}">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approve Modal -->
                <div class="modal fade" id="approveModal{{ $payment->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Approve Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('ceo.payments.approve', $payment) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <p>Are you sure you want to approve this payment of <strong>UGX {{ number_format($payment->amount, 2) }}</strong>?</p>
                                    <div class="mb-3">
                                        <label class="form-label">Upload Payment Voucher (Optional)</label>
                                        <input type="file" class="form-control" name="payment_voucher" 
                                               accept=".pdf,.jpg,.png">
                                        <small class="text-muted">PDF, JPG, or PNG files (max 2MB)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" name="ceo_notes" rows="3" 
                                                  placeholder="Add any notes about this approval..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Approve Payment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal{{ $payment->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reject Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('ceo.payments.reject', $payment) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <p>Are you sure you want to reject this payment of <strong>UGX {{ number_format($payment->amount, 2) }}</strong>?</p>
                                    <div class="mb-3">
                                        <label class="form-label">Reason for Rejection *</label>
                                        <textarea class="form-control" name="ceo_notes" rows="3" 
                                                  placeholder="Please provide a reason for rejecting this payment..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Reject Payment</button>
                                </div>
                            </form>
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