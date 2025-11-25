@extends('project_manager.layouts.app')

@section('title', 'Edit Requisition ' . $requisition->ref)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Requisition: {{ $requisition->ref }}</h5>
                        <a href="{{ route('project_manager.requisitions.show', $requisition) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Requisition
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> You can only edit quantities. Prices will be determined by Procurement based on supplier quotes.
                    </div>

                    <form action="{{ route('project_manager.requisitions.update', $requisition) }}" method="POST" enctype="multipart/form-data" id="requisitionForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required {{ $requisition->status !== 'pending' ? 'disabled' : '' }}>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id', $requisition->project_id) == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }} ({{ $project->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($requisition->status !== 'pending')
                                        <input type="hidden" name="project_id" value="{{ $requisition->project_id }}">
                                    @endif
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="urgency" class="form-label">Urgency <span class="text-danger">*</span></label>
                                    <select name="urgency" id="urgency" class="form-select @error('urgency') is-invalid @enderror" required {{ $requisition->status !== 'pending' ? 'disabled' : '' }}>
                                        <option value="">Select Urgency</option>
                                        <option value="low" {{ old('urgency', $requisition->urgency) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('urgency', $requisition->urgency) == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('urgency', $requisition->urgency) == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @if($requisition->status !== 'pending')
                                        <input type="hidden" name="urgency" value="{{ $requisition->urgency }}">
                                    @endif
                                    @error('urgency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label">Reason/Purpose <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" placeholder="Explain the purpose of this requisition and why these items are needed..." required {{ $requisition->status !== 'pending' ? 'readonly' : '' }}>{{ old('reason', $requisition->reason) }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Items Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Requisition Items <span class="text-danger">*</span></h6>
                                @if($requisition->status === 'pending')
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                                        <i class="bi bi-plus-circle"></i> Add Item
                                    </button>
                                @endif
                            </div>
                            
                            <div id="items-container">
                                @foreach($requisition->items as $index => $item)
                                    <div class="item-row border rounded p-3 mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label">Item Name</label>
                                                <input type="text" class="form-control" value="{{ $item->name }}" readonly>
                                                <input type="hidden" name="items[{{ $index }}][name]" value="{{ $item->name }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" step="0.01" min="0.01" value="{{ old('items.' . $index . '.quantity', $item->quantity) }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit</label>
                                                <input type="text" class="form-control" value="{{ $item->unit }}" readonly>
                                                <input type="hidden" name="items[{{ $index }}][unit]" value="{{ $item->unit }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Unit Price</label>
                                                <input type="text" class="form-control" value="UGX {{ number_format($item->unit_price, 2) }}" readonly>
                                                <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Total</label>
                                                <input type="text" class="form-control item-total" value="{{ number_format($item->total_price, 2) }}" readonly>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <label class="form-label">Notes (Optional)</label>
                                                <input type="text" name="items[{{ $index }}][notes]" class="form-control" value="{{ old('items.' . $index . '.notes', $item->notes) }}" placeholder="Additional notes...">
                                            </div>
                                        </div>
                                        @if($requisition->status === 'pending' && $index > 0)
                                            <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-item">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="text-end mt-3">
                                <strong>Estimated Total: UGX <span id="grand-total">{{ number_format($requisition->estimated_total, 2) }}</span></strong>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('project_manager.requisitions.show', $requisition) }}" class="btn btn-outline-secondary">Cancel</a>
                            @if($requisition->status === 'pending')
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Requisition
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary" disabled>
                                    <i class="bi bi-lock"></i> Editing Not Allowed
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = {{ $requisition->items->count() }};
    
    // Only enable editing if requisition is pending
    @if($requisition->status === 'pending')
    
    // Add item
    document.getElementById('addItem').addEventListener('click', function() {
        const itemsContainer = document.getElementById('items-container');
        const newRow = document.createElement('div');
        newRow.className = 'item-row border rounded p-3 mb-3';
        newRow.innerHTML = `
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="items[${itemCount}][name]" class="form-control" placeholder="Enter item name" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="items[${itemCount}][quantity]" class="form-control quantity" step="0.01" min="0.01" placeholder="Qty" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit</label>
                    <input type="text" name="items[${itemCount}][unit]" class="form-control" placeholder="e.g., kg, pcs" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" name="items[${itemCount}][unit_price]" class="form-control unit-price" step="0.01" min="0" placeholder="0.00" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control item-total" readonly placeholder="0.00">
                </div>
                <div class="col-12 mt-2">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" name="items[${itemCount}][notes]" class="form-control" placeholder="Additional notes...">
                </div>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-item">
                <i class="bi bi-trash"></i> Remove
            </button>
        `;
        itemsContainer.appendChild(newRow);
        itemCount++;
        
        // Add event listeners to new inputs
        addCalculationListeners(newRow);
    });
    
    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('.item-row');
            const itemsContainer = document.getElementById('items-container');
            const allRows = itemsContainer.querySelectorAll('.item-row');
            
            if (allRows.length > 1) {
                row.remove();
                // Re-index all items
                reindexItems();
                calculateGrandTotal();
            } else {
                alert('You must have at least one item in the requisition.');
            }
        }
    });

    // Re-index all items after removal
    function reindexItems() {
        const itemsContainer = document.getElementById('items-container');
        const rows = itemsContainer.querySelectorAll('.item-row');
        itemCount = 0;
        
        rows.forEach((row, index) => {
            // Update all input names with new index
            const nameInput = row.querySelector('input[name*="[name]"]');
            const quantityInput = row.querySelector('input[name*="[quantity]"]');
            const unitInput = row.querySelector('input[name*="[unit]"]');
            const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
            const notesInput = row.querySelector('input[name*="[notes]"]');
            
            if (nameInput) nameInput.name = `items[${index}][name]`;
            if (quantityInput) quantityInput.name = `items[${index}][quantity]`;
            if (unitInput) unitInput.name = `items[${index}][unit]`;
            if (unitPriceInput) unitPriceInput.name = `items[${index}][unit_price]`;
            if (notesInput) notesInput.name = `items[${index}][notes]`;
        });
        
        itemCount = rows.length;
    }
    
    // Calculate totals
    function addCalculationListeners(row) {
        const quantityInput = row.querySelector('.quantity');
        const unitPriceInput = row.querySelector('.unit-price');
        const totalInput = row.querySelector('.item-total');
        
        function calculateItemTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            const total = quantity * unitPrice;
            totalInput.value = total.toFixed(2);
            calculateGrandTotal();
        }
        
        if (quantityInput && unitPriceInput) {
            quantityInput.addEventListener('input', calculateItemTotal);
            unitPriceInput.addEventListener('input', calculateItemTotal);
        }
    }
    
    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-total').forEach(input => {
            grandTotal += parseFloat(input.value) || 0;
        });
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }
    
    // Initialize all calculations
    function initializeCalculations() {
        document.querySelectorAll('.item-row').forEach(row => {
            addCalculationListeners(row);
        });
        calculateGrandTotal();
    }
    
    // Initialize calculation for existing rows
    initializeCalculations();
    
    @endif
});
</script>
@endpush