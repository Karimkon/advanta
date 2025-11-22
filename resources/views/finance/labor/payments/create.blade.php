{{-- resources/views/finance/labor/payments/create.blade.php - UPDATED --}}
@extends('finance.layouts.app')

@section('title', 'Record Labor Payment - ' . $worker->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Record Labor Payment</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.labor.index') }}">Labor Workers</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.labor.show', $worker) }}">{{ $worker->name }}</a></li>
                    <li class="breadcrumb-item active">Record Payment</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.labor.show', $worker) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.labor.payments.store', $worker) }}" method="POST">
                        @csrf

                        <!-- Worker Information -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-person-badge"></i> Worker Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Name:</strong> {{ $worker->name }}<br>
                                    <strong>Role:</strong> {{ $worker->role }}
                                    @if($worker->nssf_number)
                                    <br><strong>NSSF No:</strong> {{ $worker->nssf_number }}
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <strong>Project:</strong> {{ $worker->project->name }}<br>
                                    <strong>Rate:</strong> UGX {{ number_format($worker->current_rate, 2) }} /{{ $worker->payment_frequency === 'daily' ? 'day' : 'month' }}
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="period_start" class="form-label">Period Start *</label>
                                <input type="date" class="form-control" id="period_start" name="period_start" 
                                       value="{{ old('period_start', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="period_end" class="form-label">Period End *</label>
                                <input type="date" class="form-control" id="period_end" name="period_end" 
                                       value="{{ old('period_end', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="days_worked" class="form-label">Days Worked *</label>
                                <input type="number" class="form-control" id="days_worked" name="days_worked" 
                                       value="{{ old('days_worked', 1) }}" min="1" max="31" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gross_amount" class="form-label">Gross Amount (UGX) *</label>
                                <input type="number" step="0.01" class="form-control" id="gross_amount" name="gross_amount" 
                                       value="{{ old('gross_amount') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nssf_amount" class="form-label">NSSF Amount (UGX) *</label>
                                <input type="number" step="0.01" class="form-control" id="nssf_amount" name="nssf_amount" 
                                       value="{{ old('nssf_amount', 0) }}" required>
                                <small class="text-muted">10% of gross amount is UGX <span id="suggested_nssf">0.00</span></small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Net Amount (UGX)</label>
                                <input type="text" class="form-control bg-light" id="net_amount_display" readonly>
                                <input type="hidden" id="net_amount" name="net_amount">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Payment Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          placeholder="Describe what this payment is for (e.g., Weekly wages, Daily work...)" required>{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" 
                                          placeholder="Any additional notes about this payment...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Amount Calculation -->
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-calculator"></i> Amount Calculation</h6>
                            <div class="d-flex justify-content-between">
                                <span>Gross Amount:</span>
                                <span id="gross_amount_display">UGX 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>NSSF Deduction:</span>
                                <span id="nssf_amount_display">UGX 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Net Amount (Payable):</span>
                                <span id="net_amount_summary">UGX 0.00</span>
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
            <!-- Worker Summary -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Worker Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong> {{ $worker->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Project:</strong> {{ $worker->project->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Role:</strong> {{ $worker->role }}
                    </div>
                    <div class="mb-3">
                        <strong>Payment Frequency:</strong>
                        <span class="badge bg-secondary text-capitalize">{{ $worker->payment_frequency }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Current Rate:</strong>
                        <span class="text-success">
                            UGX {{ number_format($worker->current_rate, 2) }} 
                            <small class="text-muted">/{{ $worker->payment_frequency === 'daily' ? 'day' : 'month' }}</small>
                        </span>
                    </div>
                    @if($worker->nssf_number)
                    <div class="mb-3">
                        <strong>NSSF Number:</strong> {{ $worker->nssf_number }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Total Paid:</strong>
                        <span class="text-primary">UGX {{ number_format($worker->total_paid, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grossAmountInput = document.getElementById('gross_amount');
    const nssfAmountInput = document.getElementById('nssf_amount');
    const netAmountDisplay = document.getElementById('net_amount_display');
    const netAmountInput = document.getElementById('net_amount');
    const grossDisplay = document.getElementById('gross_amount_display');
    const nssfDisplay = document.getElementById('nssf_amount_display');
    const netSummary = document.getElementById('net_amount_summary');
    const suggestedNssf = document.getElementById('suggested_nssf');

    function calculateAmounts() {
        const grossAmount = parseFloat(grossAmountInput.value) || 0;
        const nssfAmount = parseFloat(nssfAmountInput.value) || 0;
        const netAmount = grossAmount - nssfAmount;
        
        // Calculate suggested NSSF (10%)
        const suggestedNssfAmount = grossAmount * 0.10;
        
        // Update displays
        grossDisplay.textContent = 'UGX ' + grossAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        nssfDisplay.textContent = 'UGX ' + nssfAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        netAmountDisplay.value = 'UGX ' + netAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        netSummary.textContent = 'UGX ' + netAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        netAmountInput.value = netAmount;

        // Update suggested NSSF
        suggestedNssf.textContent = suggestedNssfAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Auto-fill NSSF if empty
        if (nssfAmount === 0 && grossAmount > 0) {
            nssfAmountInput.value = suggestedNssfAmount;
            calculateAmounts(); // Recalculate
        }
    }

    grossAmountInput.addEventListener('input', calculateAmounts);
    nssfAmountInput.addEventListener('input', calculateAmounts);
    calculateAmounts(); // Initial calculation
});
</script>
@endsection