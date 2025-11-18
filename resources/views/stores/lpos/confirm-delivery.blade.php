@extends('stores.layouts.app')

@section('title', 'Confirm LPO Delivery - ' . $lpo->lpo_number)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle"></i> Confirm LPO Delivery - {{ $lpo->lpo_number }}
                        </h5>
                        <a href="{{ route('stores.lpos.show', ['store' => $store, 'lpo' => $lpo]) }}" 
                           class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to LPO
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('stores.lpos.process-delivery', ['store' => $store, 'lpo' => $lpo]) }}" method="POST" id="deliveryForm">
                        @csrf

                        <!-- LPO Summary -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Delivery Confirmation</h6>
                            <p class="mb-0">Please verify the actual quantities received for each item. Update quantities if different from ordered amounts. Only received quantities will be added to inventory and used for payment processing.</p>
                        </div>

                        <!-- Received Items -->
                        <div class="mb-4">
                            <h5 class="mb-3">Received Items</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Item Description</th>
                                            <th width="15%">Ordered Qty</th>
                                            <th width="15%">Received Qty</th>
                                            <th width="10%">Unit</th>
                                            <th width="15%">Condition</th>
                                            <th width="15%">Unit Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lpo->items as $index => $item)
                                            <tr class="item-row">
                                                <td>
                                                    <strong>{{ $item->description }}</strong>
                                                    <input type="hidden" name="received_items[{{ $item->id }}][lpo_item_id]" value="{{ $item->id }}">
                                                </td>
                                                <td>
                                                    <span class="ordered-quantity">{{ $item->quantity }}</span>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           name="received_items[{{ $item->id }}][quantity_received]" 
                                                           class="form-control form-control-sm received-quantity" 
                                                           value="{{ $item->quantity }}"
                                                           min="0" 
                                                           max="{{ $item->quantity * 2 }}" 
                                                           step="0.01"
                                                           required
                                                           onchange="updateReceivedStatus(this)">
                                                    <small class="text-muted received-status" id="status-{{ $item->id }}">
                                                        Full delivery
                                                    </small>
                                                </td>
                                                <td>{{ $item->unit }}</td>
                                                <td>
                                                    <select name="received_items[{{ $item->id }}][condition]" class="form-select form-select-sm condition-select" onchange="updateConditionStatus(this)">
                                                        <option value="good">Good</option>
                                                        <option value="damaged">Damaged</option>
                                                        <option value="partial_damage">Partial Damage</option>
                                                        <option value="expired">Expired</option>
                                                        <option value="rejected">Rejected</option>
                                                    </select>
                                                </td>
                                                <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Delivery Summary -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Delivery Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Total Ordered:</strong> <span id="total-ordered">0</span> units</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Total Received:</strong> <span id="total-received" class="text-success">0</span> units</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Delivery Rate:</strong> <span id="delivery-rate" class="text-primary">100%</span></p>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="alert alert-warning" id="partial-delivery-alert" style="display: none;">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Partial Delivery:</strong> Some items were not fully received. Finance will process payment based on actual received quantities only.
                                        </div>
                                        <div class="alert alert-danger" id="rejected-delivery-alert" style="display: none;">
                                            <i class="bi bi-x-circle"></i>
                                            <strong>Items Rejected:</strong> Some items were rejected and will not be added to inventory.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Notes -->
                        <div class="mb-4">
                            <label for="delivery_notes" class="form-label">Delivery Notes (Optional)</label>
                            <textarea name="delivery_notes" id="delivery_notes" class="form-control" rows="3" 
                                      placeholder="Any notes about the delivery, condition of items, supplier performance, reasons for partial delivery or rejection, etc..."></textarea>
                        </div>

                        <!-- Confirmation -->
                        <div class="alert alert-warning">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmation" required>
                                <label class="form-check-label" for="confirmation">
                                    I confirm that I have physically received these items in the store and verified their quantities and condition. 
                                    I understand that payment to the supplier will be based on the actual received quantities only.
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('stores.lpos.show', ['store' => $store, 'lpo' => $lpo]) }}" 
                               class="btn btn-outline-secondary">Cancel</a>
                            <div>
                                <button type="button" class="btn btn-warning me-2" onclick="markAllAsRejected()">
                                    <i class="bi bi-x-circle"></i> Mark All as Not Received
                                </button>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="bi bi-check-circle"></i> Confirm Delivery & Update Inventory
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});

function updateReceivedStatus(input) {
    const itemId = input.name.match(/\[(\d+)\]/)[1];
    const receivedQty = parseFloat(input.value) || 0;
    const orderedQty = parseFloat(input.closest('tr').querySelector('.ordered-quantity').textContent);
    const statusElement = document.getElementById(`status-${itemId}`);
    
    if (receivedQty === 0) {
        statusElement.textContent = 'Not received';
        statusElement.className = 'text-muted received-status';
        input.closest('tr').classList.add('table-danger');
    } else if (receivedQty < orderedQty) {
        statusElement.textContent = `Partial (${((receivedQty/orderedQty)*100).toFixed(1)}%)`;
        statusElement.className = 'text-warning received-status';
        input.closest('tr').classList.remove('table-danger');
    } else if (receivedQty === orderedQty) {
        statusElement.textContent = 'Full delivery';
        statusElement.className = 'text-success received-status';
        input.closest('tr').classList.remove('table-danger');
    } else {
        statusElement.textContent = `Over delivery (${receivedQty - orderedQty} extra)`;
        statusElement.className = 'text-info received-status';
        input.closest('tr').classList.remove('table-danger');
    }
    
    calculateTotals();
}

function updateConditionStatus(select) {
    const row = select.closest('tr');
    if (select.value === 'rejected') {
        // If rejected, set quantity to 0
        const quantityInput = row.querySelector('.received-quantity');
        quantityInput.value = 0;
        quantityInput.dispatchEvent(new Event('change'));
        row.classList.add('table-danger');
    } else {
        row.classList.remove('table-danger');
    }
    calculateTotals();
}

function calculateTotals() {
    let totalOrdered = 0;
    let totalReceived = 0;
    let hasPartial = false;
    let hasRejected = false;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const orderedQty = parseFloat(row.querySelector('.ordered-quantity').textContent);
        const receivedQty = parseFloat(row.querySelector('.received-quantity').value) || 0;
        const condition = row.querySelector('.condition-select').value;
        
        totalOrdered += orderedQty;
        totalReceived += receivedQty;
        
        if (receivedQty < orderedQty && condition !== 'rejected') {
            hasPartial = true;
        }
        
        if (condition === 'rejected' || receivedQty === 0) {
            hasRejected = true;
        }
    });
    
    document.getElementById('total-ordered').textContent = totalOrdered.toFixed(2);
    document.getElementById('total-received').textContent = totalReceived.toFixed(2);
    
    const deliveryRate = totalOrdered > 0 ? (totalReceived / totalOrdered * 100) : 0;
    document.getElementById('delivery-rate').textContent = deliveryRate.toFixed(1) + '%';
    
    // Show/hide alerts
    document.getElementById('partial-delivery-alert').style.display = hasPartial ? 'block' : 'none';
    document.getElementById('rejected-delivery-alert').style.display = hasRejected ? 'block' : 'none';
    
    // Update submit button text based on delivery status
    const submitBtn = document.getElementById('submitBtn');
    if (totalReceived === 0) {
        submitBtn.innerHTML = '<i class="bi bi-x-circle"></i> Confirm No Delivery Received';
        submitBtn.className = 'btn btn-danger';
    } else if (hasPartial || hasRejected) {
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Partial Delivery';
        submitBtn.className = 'btn btn-warning';
    } else {
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Full Delivery';
        submitBtn.className = 'btn btn-success';
    }
}

function markAllAsRejected() {
    if (confirm('Mark all items as not received? This will set all quantities to 0 and mark as rejected.')) {
        document.querySelectorAll('.item-row').forEach(row => {
            const quantityInput = row.querySelector('.received-quantity');
            const conditionSelect = row.querySelector('.condition-select');
            
            quantityInput.value = 0;
            conditionSelect.value = 'rejected';
            
            // Trigger change events
            quantityInput.dispatchEvent(new Event('change'));
            conditionSelect.dispatchEvent(new Event('change'));
        });
    }
}

// Initialize all inputs on page load
document.querySelectorAll('.received-quantity').forEach(input => {
    input.dispatchEvent(new Event('change'));
});
</script>

<style>
.table-danger {
    background-color: #f8d7da;
}
.received-status {
    font-size: 0.75rem;
    display: block;
    margin-top: 2px;
}
</style>
@endsection