@extends('stores.layouts.app')

@section('title', 'Process Store Release - ' . $store->display_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Process Store Release</h2>
            <p class="text-muted mb-0">Release items for requisition: <strong>{{ $requisition->ref }}</strong></p>
        </div>
        <a href="{{ route('stores.releases.index', $store) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Releases
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('stores.releases.store', [$store, $requisition]) }}" method="POST" id="releaseForm">
        @csrf
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Requisition Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Project:</strong> {{ $requisition->project->name }}</p>
                        <p><strong>Requested By:</strong> {{ $requisition->requester->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Urgency:</strong> 
                            <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                {{ ucfirst($requisition->urgency) }}
                            </span>
                        </p>
                        <p><strong>Date:</strong> {{ $requisition->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Items to Release</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Requested Qty</th>
                                <th>Available Stock</th>
                                <th>Quantity to Release</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requisition->items as $index => $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->notes)
                                            <br><small class="text-muted">{{ $item->notes }}</small>
                                        @endif
                                        <!-- ADD THESE HIDDEN FIELDS -->
                                        <input type="hidden" name="items[{{ $index }}][requisition_item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $index }}][inventory_item_id]" value="{{ $item->inventory_item ? $item->inventory_item->id : '' }}">
                                        <input type="hidden" name="items[{{ $index }}][quantity_requested]" value="{{ $item->quantity }}">
                                    </td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>
                                        <span class="badge {{ $item->can_fulfill ? 'bg-success' : 'bg-danger' }}">
                                            {{ $item->available_stock }} {{ $item->unit }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->inventory_item && $item->available_stock > 0)
                                            <input type="number" 
                                                   name="items[{{ $index }}][quantity_released]" 
                                                   class="form-control quantity-input" 
                                                   value="{{ min($item->quantity, $item->available_stock) }}"
                                                   max="{{ $item->available_stock }}"
                                                   min="0"
                                                   step="0.01"
                                                   required
                                                   onchange="updateStatus(this, {{ $item->quantity }}, {{ $item->available_stock }})">
                                        @else
                                            <input type="number" 
                                                   class="form-control" 
                                                   value="0"
                                                   min="0" 
                                                   max="0"
                                                   readonly
                                                   disabled>
                                            <input type="hidden" name="items[{{ $index }}][quantity_released]" value="0">
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->can_fulfill)
                                            <span class="badge bg-success">Can Fulfill</span>
                                        @elseif($item->available_stock > 0)
                                            <span class="badge bg-warning">Partial</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
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
                <div class="mb-3">
                    <label class="form-label">Release Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Add any notes about this release...">{{ old('notes') }}</textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('stores.releases.index', $store) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Complete Release
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(input, requestedQty, availableQty) {
    const releasedQty = parseFloat(input.value) || 0;
    const row = input.closest('tr');
    let statusCell = row.querySelector('td:last-child');
    
    if (releasedQty === 0) {
        statusCell.innerHTML = '<span class="badge bg-secondary">Not Released</span>';
    } else if (releasedQty >= requestedQty) {
        statusCell.innerHTML = '<span class="badge bg-success">Full Release</span>';
    } else if (releasedQty > 0) {
        statusCell.innerHTML = '<span class="badge bg-warning">Partial Release</span>';
    } else {
        statusCell.innerHTML = '<span class="badge bg-danger">Cannot Release</span>';
    }
}

// Form validation
document.getElementById('releaseForm').addEventListener('submit', function(e) {
    let canRelease = false;
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(input => {
        if (parseFloat(input.value) > 0) {
            canRelease = true;
        }
    });
    
    if (!canRelease) {
        e.preventDefault();
        alert('Please specify quantities to release for at least one item.');
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    return true;
});

// Initialize status on page load
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        const row = input.closest('tr');
        const requestedQty = parseFloat(row.querySelector('input[name*="quantity_requested"]').value);
        const availableQty = parseFloat(input.max);
        updateStatus(input, requestedQty, availableQty);
    });
});
</script>

<style>
.quantity-input:invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.quantity-input:valid {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}
</style>
@endpush