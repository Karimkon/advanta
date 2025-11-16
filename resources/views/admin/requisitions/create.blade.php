@extends('admin.layouts.app')

@section('title', 'Create New Requisition')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create New Requisition</h5>
                        <a href="{{ route('admin.requisitions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Requisitions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.requisitions.store') }}" method="POST" enctype="multipart/form-data" id="requisitionForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }} ({{ $project->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="requested_by" class="form-label">Requested By <span class="text-danger">*</span></label>
                                    <select name="requested_by" id="requested_by" class="form-select @error('requested_by') is-invalid @enderror" required>
                                        <option value="">Select Requester</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('requested_by') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->role }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('requested_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="urgency" class="form-label">Urgency <span class="text-danger">*</span></label>
                                    <select name="urgency" id="urgency" class="form-select @error('urgency') is-invalid @enderror" required>
                                        <option value="">Select Urgency</option>
                                        <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('urgency') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                    @error('urgency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Attachments</label>
                                    <input type="file" name="attachments[]" id="attachments" class="form-control @error('attachments') is-invalid @enderror" multiple>
                                    <small class="text-muted">You can select multiple files (Max: 10MB each)</small>
                                    @error('attachments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="form-label">Reason/Purpose <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" placeholder="Explain the purpose of this requisition..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Items Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Requisition Items <span class="text-danger">*</span></h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                            </div>
                            
                            <div id="items-container">
                                <!-- Initial item row -->
                                <div class="item-row border rounded p-3 mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <label class="form-label">Item Name</label>
                                            <input type="text" name="items[0][name]" class="form-control" placeholder="Enter item name" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" name="items[0][quantity]" class="form-control quantity" step="0.01" min="0.01" placeholder="Qty" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unit</label>
                                            <input type="text" name="items[0][unit]" class="form-control" placeholder="e.g., kg, pcs" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unit Price</label>
                                            <input type="number" name="items[0][unit_price]" class="form-control unit-price" step="0.01" min="0" placeholder="0.00" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Total</label>
                                            <input type="text" class="form-control item-total" readonly placeholder="0.00">
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label">Notes (Optional)</label>
                                            <input type="text" name="items[0][notes]" class="form-control" placeholder="Additional notes...">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-item" style="display: none;">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <strong>Estimated Total: UGX <span id="grand-total">0.00</span></strong>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.requisitions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Requisition
                            </button>
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
    let itemCount = 1;
    
    // Add new item row
    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('items-container');
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
        container.appendChild(newRow);
        itemCount++;
        
        // Add event listeners to new inputs
        addCalculationListeners(newRow);
    });
    
    // Remove item row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('.item-row');
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateGrandTotal();
            }
        }
    });
    
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
        
        quantityInput.addEventListener('input', calculateItemTotal);
        unitPriceInput.addEventListener('input', calculateItemTotal);
    }
    
    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-total').forEach(input => {
            grandTotal += parseFloat(input.value) || 0;
        });
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }
    
    // Initialize calculation for first row
    addCalculationListeners(document.querySelector('.item-row'));
    
    // Show remove button for first row if there are multiple rows
    document.getElementById('addItem').addEventListener('click', function() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.style.display = 'inline-block';
        });
    });
});
</script>
@endpush