{{-- resources/views/finance/subcontractors/payments/create.blade.php --}}
@extends('finance.layouts.app')

@section('title', 'Record Subcontractor Payment')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Record Subcontractor Payment</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.subcontractors.index') }}">Subcontractors</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.subcontractors.show', $projectSubcontractor->subcontractor) }}">{{ $projectSubcontractor->subcontractor->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.subcontractors.ledger', $projectSubcontractor) }}">Ledger</a></li>
                    <li class="breadcrumb-item active">Record Payment</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.subcontractors.ledger', $projectSubcontractor) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Ledger
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.subcontractors.payments.store', $projectSubcontractor) }}" method="POST">
                        @csrf

                        <!-- Contract Information -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-info-circle"></i> Contract Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Subcontractor:</strong> {{ $projectSubcontractor->subcontractor->name }}<br>
                                    <strong>Project:</strong> {{ $projectSubcontractor->project->name }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Contract Amount:</strong> UGX {{ number_format($projectSubcontractor->contract_amount, 2) }}<br>
                                    <strong>Balance:</strong> UGX {{ number_format($projectSubcontractor->balance, 2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Payment Amount (UGX) *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                       value="{{ old('amount') }}" max="{{ $projectSubcontractor->balance }}" required>
                                <small class="text-muted">Maximum: UGX {{ number_format($projectSubcontractor->balance, 2) }}</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_type" class="form-label">Payment Type *</label>
                                <select class="form-select" id="payment_type" name="payment_type" required>
                                    <option value="">Select Type</option>
                                    <option value="advance" {{ old('payment_type') == 'advance' ? 'selected' : '' }}>Advance Payment</option>
                                    <option value="progress" {{ old('payment_type') == 'progress' ? 'selected' : '' }}>Progress Payment</option>
                                    <option value="final" {{ old('payment_type') == 'final' ? 'selected' : '' }}>Final Payment</option>
                                    <option value="retention" {{ old('payment_type') == 'retention' ? 'selected' : '' }}>Retention Payment</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Payment Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Describe what this payment is for..." required>{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" 
                                       value="{{ old('reference_number') }}" placeholder="Bank reference, cheque number, etc.">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" 
                                          placeholder="Any additional notes about this payment...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- New Balance Preview -->
                        <div class="alert alert-warning">
                            <div class="d-flex justify-content-between">
                                <strong>Current Balance:</strong>
                                <span>UGX {{ number_format($projectSubcontractor->balance, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Payment Amount:</strong>
                                <span id="payment_amount_display">UGX 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <strong>New Balance:</strong>
                                <span id="new_balance_display">UGX {{ number_format($projectSubcontractor->balance, 2) }}</span>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cash-coin"></i> Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contract Summary -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Contract Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Contract No:</strong> {{ $projectSubcontractor->contract_number }}
                    </div>
                    <div class="mb-3">
                        <strong>Subcontractor:</strong> {{ $projectSubcontractor->subcontractor->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Project:</strong> {{ $projectSubcontractor->project->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Work Description:</strong> 
                        <p class="mt-1">{{ $projectSubcontractor->work_description }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Start Date:</strong> {{ $projectSubcontractor->start_date->format('M d, Y') }}
                    </div>
                    @if($projectSubcontractor->terms)
                    <div class="mb-3">
                        <strong>Terms:</strong> 
                        <p class="mt-1">{{ $projectSubcontractor->terms }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            @if($projectSubcontractor->payments->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    @foreach($projectSubcontractor->payments->take(3) as $payment)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <strong class="text-success">UGX {{ number_format($payment->amount, 2) }}</strong>
                                <small class="text-muted">{{ $payment->payment_date->format('M d') }}</small>
                            </div>
                            <small class="text-muted">{{ $payment->description }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const currentBalance = {{ $projectSubcontractor->balance }};
    const paymentDisplay = document.getElementById('payment_amount_display');
    const newBalanceDisplay = document.getElementById('new_balance_display');

    function updateBalancePreview() {
        const paymentAmount = parseFloat(amountInput.value) || 0;
        const newBalance = currentBalance - paymentAmount;

        paymentDisplay.textContent = 'UGX ' + paymentAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        newBalanceDisplay.textContent = 'UGX ' + newBalance.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Color coding
        if (newBalance <= 0) {
            newBalanceDisplay.className = 'text-success';
        } else if (newBalance < currentBalance * 0.2) {
            newBalanceDisplay.className = 'text-warning';
        } else {
            newBalanceDisplay.className = 'text-danger';
        }
    }

    amountInput.addEventListener('input', updateBalancePreview);
    updateBalancePreview(); // Initial calculation
});
</script>
@endsection