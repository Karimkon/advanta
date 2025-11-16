@extends('finance.layouts.app')

@section('title', 'Process Payment')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Process Payment</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.payments.pending') }}">Pending Payments</a></li>
                    <li class="breadcrumb-item active">Process Payment</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.payments.pending') }}" class="btn btn-outline-secondary">
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
                    <form action="{{ route('finance.payments.store', $requisition) }}" method="POST">
                        @csrf

                        <!-- Requisition Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Requisition:</strong> {{ $requisition->ref }}</p>
                                <p><strong>Project:</strong> {{ $requisition->project->name ?? 'N/A' }}</p>
                                <p><strong>Supplier:</strong> {{ $requisition->supplier->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong> UGX {{ number_format($requisition->estimated_total, 2) }}</p>
                                <p><strong>LPO Number:</strong> {{ $requisition->lpo->lpo_number ?? 'N/A' }}</p>
                                <p><strong>Created:</strong> {{ $requisition->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Payment Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                       value="{{ $requisition->estimated_total }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number" 
                                       placeholder="e.g., Bank reference, transaction ID">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tax_amount" class="form-label">Tax Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="tax_amount" name="tax_amount" 
                                       value="0" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="other_charges" class="form-label">Other Charges</label>
                                <input type="number" step="0.01" class="form-control" id="other_charges" name="other_charges" 
                                       value="0">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Payment Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Any additional notes about this payment..."></textarea>
                            </div>
                        </div>

                        <!-- Total Calculation -->
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between">
                                <strong>Payment Amount:</strong>
                                <span id="payment_amount_display">UGX 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Tax Amount:</strong>
                                <span id="tax_amount_display">UGX 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Other Charges:</strong>
                                <span id="other_charges_display">UGX 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <strong>Total Amount:</strong>
                                <span id="total_amount_display">UGX 0.00</span>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-credit-card"></i> Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Requisition Summary -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Requisition Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Reference:</strong> {{ $requisition->ref }}
                    </div>
                    <div class="mb-3">
                        <strong>Project:</strong> {{ $requisition->project->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Supplier:</strong> {{ $requisition->supplier->name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Type:</strong> 
                        <span class="badge bg-{{ $requisition->type === 'store' ? 'info' : 'primary' }}">
                            {{ ucfirst($requisition->type) }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Urgency:</strong> 
                        <span class="badge {{ $requisition->urgency === 'high' ? 'bg-danger' : ($requisition->urgency === 'medium' ? 'bg-warning' : 'bg-secondary') }}">
                            {{ ucfirst($requisition->urgency) }}
                        </span>
                    </div>
                    <div>
                        <strong>Created:</strong> {{ $requisition->created_at->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Items Summary -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Requisition Items</h5>
                </div>
                <div class="card-body">
                    @foreach($requisition->items as $item)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $item->name }}</strong><br>
                            <small class="text-muted">
                                {{ $item->quantity }} {{ $item->unit }} × UGX {{ number_format($item->unit_price, 2) }}
                            </small><br>
                            <small class="text-success">
                                Total: UGX {{ number_format($item->total_price, 2) }}
                            </small>
                        </div>
                    @endforeach
                    <div class="mt-2 fw-bold">
                        Grand Total: UGX {{ number_format($requisition->estimated_total, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function calculateTotal() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
            const otherCharges = parseFloat(document.getElementById('other_charges').value) || 0;
            const total = amount + taxAmount + otherCharges;

            document.getElementById('payment_amount_display').textContent = 'UGX ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('tax_amount_display').textContent = 'UGX ' + taxAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('other_charges_display').textContent = 'UGX ' + otherCharges.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('total_amount_display').textContent = 'UGX ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        // Add event listeners
        document.getElementById('amount').addEventListener('input', calculateTotal);
        document.getElementById('tax_amount').addEventListener('input', calculateTotal);
        document.getElementById('other_charges').addEventListener('input', calculateTotal);

        // Initial calculation
        calculateTotal();
    });
</script>
@endsection