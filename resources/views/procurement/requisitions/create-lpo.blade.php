@extends('procurement.layouts.app')

@section('title', 'Create LPO - ' . $requisition->ref)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create Local Purchase Order</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('procurement.requisitions.index') }}">Requisitions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('procurement.requisitions.show', $requisition) }}">{{ $requisition->ref }}</a></li>
                    <li class="breadcrumb-item active">Create LPO</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.requisitions.show', $requisition) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Requisition
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Requisition Summary -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Requisition Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Reference:</strong> {{ $requisition->ref }}</p>
                            <p class="mb-1"><strong>Project:</strong> {{ $requisition->project->name }}</p>
                            <p class="mb-1"><strong>Project Location:</strong> {{ $requisition->project->location ?? 'Katula Road Kisaasi' }}</p>
                            <p class="mb-1"><strong>Requested By:</strong> {{ $requisition->requester->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Urgency:</strong> 
                                <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                    {{ ucfirst($requisition->urgency) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Total Items:</strong> {{ $requisition->items->count() }}</p>
                            <p class="mb-1"><strong>Total Amount:</strong> UGX {{ number_format($requisition->total_cost, 2) }}</p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-success">Ready for LPO Creation</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LPO Creation Form -->
            <form action="{{ route('procurement.requisitions.create-lpo', $requisition) }}" method="POST">
                @csrf
                
                <!-- Supplier Selection -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Supplier Selection</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Select Supplier *</label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">Choose a supplier...</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" 
                                        {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} - {{ $supplier->contact_person }} ({{ $supplier->phone }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- VAT Configuration Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">VAT Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>VAT Note:</strong> Some items attract 18% VAT while others don't. 
                            Please select which items should include VAT. Finance will use this information for payment processing.
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="40%">Item Description</th>
                                        <th width="15%">Quantity</th>
                                        <th width="15%">Unit Price</th>
                                        <th width="15%">Total</th>
                                        <th width="15%">Add VAT?</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requisition->items as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ number_format($item->quantity, 3) }} {{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>UGX {{ number_format($item->total_price, 2) }}</td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="items_with_vat[]" 
                                                       value="{{ $item->id }}" 
                                                       id="vat_item_{{ $item->id }}"
                                                       class="form-check-input vat-checkbox"
                                                       data-item-id="{{ $item->id }}"
                                                       data-item-total="{{ $item->total_price }}"
                                                       {{ in_array($item->id, old('items_with_vat', [])) ? 'checked' : '' }}
                                                <label class="form-check-label small" for="vat_item_{{ $item->id }}">
                                                    18% VAT
                                                </label>
                                            </div>
                                            <input type="hidden" name="vat_rates[{{ $item->id }}]" value="18" class="vat-rate-input">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- VAT Summary -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">VAT Summary</h6>
                                        <p class="mb-1"><strong>Subtotal (Excluding VAT):</strong> <span id="subtotal-display">UGX 0.00</span></p>
                                        <p class="mb-1"><strong>VAT Amount (18%):</strong> <span id="vat-amount-display">UGX 0.00</span></p>
                                        <p class="mb-0"><strong>Grand Total:</strong> <span id="grand-total-display">UGX 0.00</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Delivery Information</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="delivery_date" class="form-label">Delivery Date *</label>
            <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                   value="{{ old('delivery_date') }}" 
                   min="{{ date('Y-m-d') }}" required>
            @error('delivery_date')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="terms" class="form-label">Terms & Conditions</label>
            <textarea class="form-control" id="terms" name="terms" rows="4" 
                      placeholder="Payment terms, delivery conditions, etc.">{{ old('terms', $defaultTerms) }}</textarea>
            <small class="text-muted">You can edit these default terms as needed</small>
            @error('terms')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2" 
                      placeholder="Any additional notes for the supplier...">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

                <!-- Form Actions -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('procurement.requisitions.show', $requisition) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-file-earmark-plus"></i> Create LPO with VAT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vatCheckboxes = document.querySelectorAll('.vat-checkbox');

    function calculateVatSummary() {
        let subtotal = 0;
        let vatAmount = 0;

        // Calculate subtotal from all items
        @foreach($requisition->items as $item)
            subtotal += {{ $item->total_price }};
        @endforeach

        // Calculate VAT only for checked items
        vatCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const itemTotal = parseFloat(checkbox.dataset.itemTotal);
                vatAmount += itemTotal * 0.18;
            }
        });

        const grandTotal = subtotal + vatAmount;

        // Update displays
        document.getElementById('subtotal-display').textContent = 'UGX ' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('vat-amount-display').textContent = 'UGX ' + vatAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('grand-total-display').textContent = 'UGX ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    // Add event listeners
    vatCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateVatSummary);
    });

    // Initial calculation
    calculateVatSummary();
});
</script>
@endpush