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
                                <p><strong>Original Amount:</strong> UGX {{ number_format($requisition->estimated_total, 2) }}</p>
                                <p><strong>Actual Received Value:</strong> UGX {{ number_format($actualAmount, 2) }}</p>
                                <p><strong>LPO Number:</strong> {{ $requisition->lpo->lpo_number ?? 'N/A' }}</p>
                                <p><strong>Created:</strong> {{ $requisition->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <!-- Received Items Summary -->
                        @if($requisition->lpo && $requisition->lpo->receivedItems->count() > 0)
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-info-circle"></i> Delivery Summary</h6>
                            <p class="mb-2">Payment should be based on actual received quantities:</p>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Ordered</th>
                                            <th>Received</th>
                                            <th>Unit Price</th>
                                            <th>Actual Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($requisition->lpo->receivedItems as $receivedItem)
                                            @if($receivedItem->lpoItem && $receivedItem->quantity_received > 0)
                                            <tr>
                                                <td>{{ $receivedItem->lpoItem->description }}</td>
                                                <td>{{ $receivedItem->quantity_ordered }}</td>
                                                <td class="{{ $receivedItem->quantity_received < $receivedItem->quantity_ordered ? 'text-warning' : 'text-success' }}">
                                                    {{ $receivedItem->quantity_received }}
                                                </td>
                                                <td>UGX {{ number_format($receivedItem->lpoItem->unit_price, 2) }}</td>
                                                <td class="fw-bold">UGX {{ number_format($receivedItem->quantity_received * $receivedItem->lpoItem->unit_price, 2) }}</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-success">
                                            <td colspan="4" class="text-end"><strong>Total Actual Value:</strong></td>
                                            <td><strong>UGX {{ number_format($actualAmount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Payment Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Payment Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                       value="{{ $actualAmount }}" required>
                                <small class="text-muted">Based on actual received quantities: UGX {{ number_format($actualAmount, 2) }}</small>
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
                        <strong>Delivery Status:</strong> 
                        <span class="badge bg-success">Delivered</span>
                    </div>
                    <div class="mb-3">
                        <strong>Original Total:</strong> UGX {{ number_format($requisition->estimated_total, 2) }}
                    </div>
                    <div class="mb-3">
                        <strong>Actual Value:</strong> UGX {{ number_format($actualAmount, 2) }}
                    </div>
                    <div>
                        <strong>Created:</strong> {{ $requisition->created_at->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Delivery Details -->
            @if($requisition->lpo && $requisition->lpo->receivedItems->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Delivery Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Delivery Date:</strong> {{ $requisition->lpo->delivery_date->format('M d, Y') }}</p>
                    <p><strong>Received Items:</strong> {{ $requisition->lpo->receivedItems->where('quantity_received', '>', 0)->count() }}</p>
                    <p><strong>Delivery Rate:</strong> 
                        {{ number_format(($requisition->lpo->receivedItems->sum('quantity_received') / $requisition->lpo->receivedItems->sum('quantity_ordered')) * 100, 1) }}%
                    </p>
                    @if($requisition->lpo->delivery_notes)
                        <p><strong>Delivery Notes:</strong> {{ $requisition->lpo->delivery_notes }}</p>
                    @endif
                </div>
            </div>
            @endif
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