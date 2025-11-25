@extends('ceo.layouts.app')

@section('title', 'Payment Details - PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Payment Details</h2>
            <p class="text-muted mb-0">PAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.payments.pending') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Pending
            </a>
            @if($payment->isPendingCeoApproval())
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle"></i> Reject
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment ID:</strong> PAY-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                            <p><strong>Payment Date:</strong> {{ $payment->formatted_paid_on }}</p>
                            <p><strong>Payment Method:</strong> 
                                <span class="badge bg-secondary text-capitalize">
                                    {{ str_replace('_', ' ', $payment->payment_method) }}
                                </span>
                            </p>
                            <p><strong>Reference Number:</strong> {{ $payment->reference ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Approval Status:</strong> 
                                <span class="badge bg-{{ $payment->getApprovalStatusBadgeClass() }}">
                                    {{ $payment->getApprovalStatusText() }}
                                </span>
                            </p>
                            <p><strong>Amount:</strong> UGX {{ number_format($payment->amount, 2) }}</p>
                            <p><strong>Processed By:</strong> {{ $payment->paidBy->name ?? 'N/A' }}</p>
                            <p><strong>Created:</strong> {{ $payment->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($payment->notes)
                    <div class="mt-3">
                        <strong>Notes:</strong>
                        <p class="mt-1">{{ $payment->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Breakdown -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Subtotal:</strong><br>
                            UGX {{ number_format($payment->amount - $payment->vat_amount - $payment->additional_costs, 2) }}
                        </div>
                        <div class="col-md-4">
                            <strong>VAT Amount:</strong><br>
                            UGX {{ number_format($payment->vat_amount, 2) }}
                            @if($payment->vat_amount > 0)
                                <br><small class="text-muted">({{ number_format($payment->getVatPercentage(), 1) }}%)</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <strong>Additional Costs:</strong><br>
                            UGX {{ number_format($payment->additional_costs, 2) }}
                            @if($payment->additional_costs_description)
                                <br><small class="text-muted">{{ $payment->additional_costs_description }}</small>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Total Amount:</strong>
                            <span class="float-end fw-bold fs-5">UGX {{ number_format($payment->amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Related Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Supplier:</strong> {{ $payment->supplier->name }}</p>
                            <p><strong>Project:</strong> {{ $payment->lpo->requisition->project->name }}</p>
                            <p><strong>LPO Number:</strong> {{ $payment->lpo->lpo_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Requisition:</strong> {{ $payment->lpo->requisition->ref }}</p>
                            <p><strong>Requisition Total:</strong> UGX {{ number_format($payment->lpo->requisition->estimated_total, 2) }}</p>
                            <p><strong>LPO Amount:</strong> UGX {{ number_format($payment->lpo->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Approval Actions -->
            @if($payment->isPendingCeoApproval())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Approval Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-circle"></i> Approve Payment
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Reject Payment
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item active">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Payment Created</strong>
                                <small class="text-muted d-block">{{ $payment->created_at->format('M d, Y H:i') }}</small>
                                <small>By: {{ $payment->paidBy->name ?? 'N/A' }}</small>
                            </div>
                        </div>
                        @if($payment->isCeoApproved())
                        <div class="timeline-item active">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <strong>CEO Approved</strong>
                                <small class="text-muted d-block">{{ $payment->ceo_approved_at->format('M d, Y H:i') }}</small>
                                <small>By: {{ $payment->ceoApprovedBy->name ?? 'N/A' }}</small>
                                @if($payment->ceo_notes)
                                    <small class="text-muted">Note: {{ $payment->ceo_notes }}</small>
                                @endif
                            </div>
                        </div>
                        @elseif($payment->isCeoRejected())
                        <div class="timeline-item active">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <strong>CEO Rejected</strong>
                                <small class="text-muted d-block">{{ $payment->ceo_approved_at->format('M d, Y H:i') }}</small>
                                <small>By: {{ $payment->ceoApprovedBy->name ?? 'N/A' }}</small>
                                @if($payment->ceo_notes)
                                    <small class="text-danger">Reason: {{ $payment->ceo_notes }}</small>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Voucher -->
            @if($payment->payment_voucher_path)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Voucher</h5>
                </div>
                <div class="card-body text-center">
                    <a href="{{ Storage::url($payment->payment_voucher_path) }}" 
                       target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-download"></i> Download Voucher
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
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
<div class="modal fade" id="rejectModal" tabindex="-1">
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

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    margin-bottom: 15px;
}

.timeline-marker {
    position: absolute;
    left: -20px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
}

.timeline-item.active .timeline-marker {
    background: #10b981;
    border-color: #10b981;
}

.timeline-content {
    padding-bottom: 10px;
}
</style>
@endsection