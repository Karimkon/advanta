@extends('finance.layouts.app')

@section('title', 'Bulk Labor Payments - ' . $project->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Bulk Labor Payments</h2>
            <p class="text-muted mb-0">{{ $project->name }} - {{ $paymentDate->format('F Y') }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.labor.bulk-payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('finance.labor.bulk-payments.store') }}" method="POST" id="bulkPaymentForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="payment_date" value="{{ date('Y-m-d') }}">
        <input type="hidden" name="payment_method" value="cash" id="payment_method">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Settings</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="calculateAll">
                        <i class="bi bi-calculator"></i> Calculate All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="applyToAll">
                        <i class="bi bi-arrow-repeat"></i> Apply Description to All
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method_select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Period</label>
                        <input type="text" class="form-control" 
                               value="{{ $paymentDate->startOfMonth()->format('M d') }} - {{ $paymentDate->endOfMonth()->format('M d, Y') }}" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Default Description</label>
                        <input type="text" class="form-control" id="default_description" 
                               value="Monthly wages for {{ $paymentDate->format('F Y') }}" 
                               placeholder="Enter default payment description">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Workers Payment Details</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Worker</th>
                                <th>Role</th>
                                <th>Frequency</th>
                                <th>Standard Rate</th>
                                <th width="120">Days Worked</th>
                                <th width="150">Gross Amount</th>
                                <th width="150">NSSF Amount</th>
                                <th width="150">Net Amount</th>
                                <th width="200">Description</th>
                                <th width="100">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workers as $index => $worker)
                                @php
                                    $existingPayment = $worker->payments->first();
                                    $defaultDays = $worker->payment_frequency === 'monthly' ? 22 : ($existingPayment ? $existingPayment->days_worked : 0);
                                    $defaultGross = $existingPayment ? $existingPayment->gross_amount : (
                                        $worker->payment_frequency === 'monthly' ? $worker->monthly_rate : 
                                        ($worker->payment_frequency === 'daily' ? $worker->daily_rate * $defaultDays : 0)
                                    );
                                    $defaultNssf = $existingPayment ? $existingPayment->nssf_amount : ($defaultGross * 0.10);
                                    $defaultNet = $defaultGross - $defaultNssf;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $worker->name }}</strong>
                                        <input type="hidden" name="payments[{{ $index }}][worker_id]" value="{{ $worker->id }}">
                                    </td>
                                    <td>{{ $worker->role }}</td>
                                    <td>
                                        <span class="badge bg-secondary text-capitalize">{{ $worker->payment_frequency }}</span>
                                    </td>
                                    <td class="text-success">
                                        UGX {{ number_format($worker->current_rate, 2) }}
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="payments[{{ $index }}][days_worked]" 
                                               class="form-control form-control-sm days-worked" 
                                               value="{{ $defaultDays }}" 
                                               min="0" 
                                               max="31"
                                               data-worker-id="{{ $worker->id }}"
                                               data-daily-rate="{{ $worker->daily_rate }}"
                                               data-monthly-rate="{{ $worker->monthly_rate }}"
                                               data-frequency="{{ $worker->payment_frequency }}">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="payments[{{ $index }}][gross_amount]" 
                                               class="form-control form-control-sm gross-amount" 
                                               value="{{ number_format($defaultGross, 2, '.', '') }}" 
                                               step="0.01"
                                               min="0"
                                               required>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="payments[{{ $index }}][nssf_amount]" 
                                               class="form-control form-control-sm nssf-amount" 
                                               value="{{ number_format($defaultNssf, 2, '.', '') }}" 
                                               step="0.01"
                                               min="0"
                                               required>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               class="form-control form-control-sm net-amount bg-light" 
                                               value="{{ number_format($defaultNet, 2) }}" 
                                               readonly>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="payments[{{ $index }}][description]" 
                                               class="form-control form-control-sm payment-description" 
                                               value="{{ $existingPayment ? $existingPayment->description : 'Monthly wages for ' . $paymentDate->format('F Y') }}"
                                               required>
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="payments[{{ $index }}][notes]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $existingPayment ? $existingPayment->notes : '' }}"
                                               placeholder="Notes">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-info">
                            <h6>Summary</h6>
                            <div class="d-flex justify-content-between">
                                <span>Total Workers:</span>
                                <strong>{{ $workers->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Gross:</span>
                                <strong id="total-gross">UGX 0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total NSSF:</span>
                                <strong id="total-nssf">UGX 0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Net:</span>
                                <strong id="total-net" class="text-success">UGX 0.00</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 text-end">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Process All Payments
                        </button>
                        <small class="d-block text-muted mt-2">
                            This will create individual payment records for each worker
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method_select');
    const defaultDescription = document.getElementById('default_description');
    const calculateAllBtn = document.getElementById('calculateAll');
    const applyToAllBtn = document.getElementById('applyToAll');
    
    // Sync payment method
    paymentMethodSelect.addEventListener('change', function() {
        document.getElementById('payment_method').value = this.value;
    });
    
    // Apply description to all
    applyToAllBtn.addEventListener('click', function() {
        const descriptionInputs = document.querySelectorAll('.payment-description');
        descriptionInputs.forEach(input => {
            input.value = defaultDescription.value;
        });
    });
    
    // Calculate all amounts based on days worked
    calculateAllBtn.addEventListener('click', function() {
        const daysInputs = document.querySelectorAll('.days-worked');
        daysInputs.forEach(input => {
            const days = parseInt(input.value) || 0;
            const dailyRate = parseFloat(input.dataset.dailyRate) || 0;
            const monthlyRate = parseFloat(input.dataset.monthlyRate) || 0;
            const frequency = input.dataset.frequency;
            
            let grossAmount = 0;
            
            if (frequency === 'daily') {
                grossAmount = days * dailyRate;
            } else if (frequency === 'monthly') {
                grossAmount = monthlyRate; // Monthly workers get full amount regardless of days
            } else if (frequency === 'weekly') {
                grossAmount = days * dailyRate;
            }
            
            // Find the gross amount input in the same row
            const row = input.closest('tr');
            const grossInput = row.querySelector('.gross-amount');
            const nssfInput = row.querySelector('.nssf-amount');
            const netInput = row.querySelector('.net-amount');
            
            if (grossInput) {
                grossInput.value = grossAmount.toFixed(2);
                // Auto-calculate NSSF (10%)
                const nssfAmount = grossAmount * 0.10;
                nssfInput.value = nssfAmount.toFixed(2);
                // Calculate net amount
                const netAmount = grossAmount - nssfAmount;
                netInput.value = netAmount.toFixed(2);
            }
        });
        
        updateTotals();
    });
    
    // Auto-calculate when gross amount changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('gross-amount')) {
            const grossAmount = parseFloat(e.target.value) || 0;
            const row = e.target.closest('tr');
            const nssfInput = row.querySelector('.nssf-amount');
            const netInput = row.querySelector('.net-amount');
            
            // Auto-calculate NSSF (10%)
            const nssfAmount = grossAmount * 0.10;
            nssfInput.value = nssfAmount.toFixed(2);
            
            // Calculate net amount
            const netAmount = grossAmount - nssfAmount;
            netInput.value = netAmount.toFixed(2);
            
            updateTotals();
        }
        
        if (e.target.classList.contains('nssf-amount') || e.target.classList.contains('gross-amount')) {
            updateTotals();
        }
    });
    
    function updateTotals() {
        let totalGross = 0;
        let totalNssf = 0;
        let totalNet = 0;
        
        const grossInputs = document.querySelectorAll('.gross-amount');
        const nssfInputs = document.querySelectorAll('.nssf-amount');
        const netInputs = document.querySelectorAll('.net-amount');
        
        grossInputs.forEach(input => {
            totalGross += parseFloat(input.value) || 0;
        });
        
        nssfInputs.forEach(input => {
            totalNssf += parseFloat(input.value) || 0;
        });
        
        netInputs.forEach(input => {
            totalNet += parseFloat(input.value) || 0;
        });
        
        document.getElementById('total-gross').textContent = 'UGX ' + totalGross.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        document.getElementById('total-nssf').textContent = 'UGX ' + totalNssf.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        document.getElementById('total-net').textContent = 'UGX ' + totalNet.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Initial calculations
    calculateAllBtn.click();
});
</script>
@endsection