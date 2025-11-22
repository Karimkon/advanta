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
                                <p><strong>Expected Total:</strong> UGX {{ number_format($breakdown['total'], 2) }}</p>
                                <p><strong>LPO Number:</strong> {{ $requisition->lpo->lpo_number ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- VAT & Tax Breakdown -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="bi bi-calculator"></i> Expected Payment Breakdown</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Subtotal:</strong> UGX {{ number_format($breakdown['subtotal'], 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>VAT Amount:</strong> UGX {{ number_format($breakdown['vat_amount'], 2) }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Total:</strong> UGX {{ number_format($breakdown['total'], 2) }}
                                </div>
                            </div>
                            @if($breakdown['vat_amount'] > 0)
                            <small class="text-muted">VAT Rate: {{ number_format($breakdown['vat_percentage'], 1) }}%</small>
                            @endif
                        </div>

                        <!-- Payment Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Payment Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" 
                                       value="{{ $breakdown['total'] }}" required>
                                <small class="text-muted">Based on actual received quantities</small>
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

                            <!-- VAT and Tax Fields -->
                            <div class="col-md-6 mb-3">
                                <label for="vat_amount" class="form-label">VAT Amount *</label>
                                <input type="number" step="0.01" class="form-control" id="vat_amount" name="vat_amount" 
                                       value="{{ $breakdown['vat_amount'] }}" required>
                                <small class="text-muted">VAT amount based on received items</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="additional_costs" class="form-label">Additional Costs</label>
                                <input type="number" step="0.01" class="form-control" id="additional_costs" name="additional_costs" 
                                       value="0" placeholder="0.00">
                                <small class="text-muted">Transport, handling fees, or other charges</small>
                            </div>

                            <!-- NEW: Additional Costs Description -->
                            <div class="col-12 mb-3">
                                <label for="additional_costs_description" class="form-label">Additional Costs Description</label>
                                <input type="text" class="form-control" id="additional_costs_description" name="additional_costs_description" 
                                       placeholder="e.g., Transport charges, Handling fees, etc.">
                                <small class="text-muted">Brief description of what the additional costs cover</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Payment Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" 
                                          placeholder="Any additional notes about this payment..."></textarea>
                            </div>
                        </div>

                        <!-- Total Calculation -->
                        <div class="alert alert-warning">
                            <div class="d-flex justify-content-between">
                                <strong>Payment Amount:</strong>
                                <span id="payment_amount_display">UGX {{ number_format($breakdown['total'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>VAT Amount:</strong>
                                <span id="vat_amount_display">UGX {{ number_format($breakdown['vat_amount'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between" id="additional_costs_row" style="display: none;">
                                <strong id="additional_costs_label">Additional Costs:</strong>
                                <span id="additional_costs_display">UGX 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <strong>Total Amount:</strong>
                                <span id="total_amount_display">UGX {{ number_format($breakdown['total'], 2) }}</span>
                            </div>
                        </div>

                        <div class="alert alert-light border">
                            <div class="text-center">
                                <strong>Amount in Words:</strong>
                                <p class="mb-0 fst-italic" id="amount_in_words">
                                    {{-- This will be populated by JavaScript --}}
                                </p>
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
                        <strong>Expected Payment:</strong> UGX {{ number_format($breakdown['total'], 2) }}
                    </div>
                    @if($breakdown['vat_amount'] > 0)
                    <div class="mb-3">
                        <strong>Includes VAT:</strong> UGX {{ number_format($breakdown['vat_amount'], 2) }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function calculateTotal() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const vatAmount = parseFloat(document.getElementById('vat_amount').value) || 0;
        const additionalCosts = parseFloat(document.getElementById('additional_costs').value) || 0;
        const total = amount;

        document.getElementById('payment_amount_display').textContent = 'UGX ' + amount.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('vat_amount_display').textContent = 'UGX ' + vatAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Show/hide additional costs row
        const additionalCostsRow = document.getElementById('additional_costs_row');
        if (additionalCosts > 0) {
            additionalCostsRow.style.display = 'flex';
            document.getElementById('additional_costs_display').textContent = 'UGX ' + additionalCosts.toLocaleString('en-US', {minimumFractionDigits: 2});
        } else {
            additionalCostsRow.style.display = 'none';
        }
        
        document.getElementById('total_amount_display').textContent = 'UGX ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        // Update amount in words
        document.getElementById('amount_in_words').textContent = convertAmountToWords(total);
    }
        

    // Amount to words conversion function
    function convertAmountToWords(amount) {
        if (amount === 0) return 'Zero Uganda Shillings Only';
        
        const units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
        const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        function convertThreeDigits(num) {
            let words = '';
            
            // Hundreds
            if (num >= 100) {
                words += units[Math.floor(num / 100)] + ' Hundred ';
                num %= 100;
            }
            
            // Tens and units
            if (num >= 20) {
                words += tens[Math.floor(num / 10)] + ' ';
                num %= 10;
            } else if (num >= 10) {
                words += teens[num - 10] + ' ';
                num = 0;
            }
            
            // Units
            if (num > 0) {
                words += units[num] + ' ';
            }
            
            return words.trim();
        }
        
        let shillings = Math.floor(amount);
        let cents = Math.round((amount - shillings) * 100);
        
        let words = '';
        
        // Millions
        if (shillings >= 1000000) {
            words += convertThreeDigits(Math.floor(shillings / 1000000)) + ' Million ';
            shillings %= 1000000;
        }
        
        // Thousands
        if (shillings >= 1000) {
            words += convertThreeDigits(Math.floor(shillings / 1000)) + ' Thousand ';
            shillings %= 1000;
        }
        
        // Hundreds
        if (shillings > 0) {
            words += convertThreeDigits(shillings) + ' ';
        }
        
        words += 'Uganda Shillings';
        
        // Cents
        if (cents > 0) {
            words += ' and ' + convertThreeDigits(cents) + ' Cents';
        }
        
        return words + ' Only';
    }

    // Add event listeners
    document.getElementById('amount').addEventListener('input', calculateTotal);
    document.getElementById('vat_amount').addEventListener('input', calculateTotal);
    document.getElementById('additional_costs').addEventListener('input', calculateTotal);
    
    // Initialize on page load
    calculateTotal();
});
</script>
@endsection