@extends('engineer.layouts.app')

@section('title', 'Create New Requisition')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Create New Requisition</h5>
                        <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Requisitions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('engineer.requisitions.store') }}" method="POST" enctype="multipart/form-data" id="requisitionForm">
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
                                    <label class="form-label">Requisition Type <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="store" {{ old('type') == 'store' ? 'selected' : '' }}>From Project Store (On-Site)</option>
                                        <option value="purchase" {{ old('type') == 'purchase' ? 'selected' : '' }}>New Purchase (Office)</option>
                                    </select>
                                    <small class="text-muted">
                                        <strong>From Project Store:</strong> Items already in store - Quick approval (Engineer → Project Manager → Store)<br>
                                        <strong>New Purchase:</strong> Need to buy from suppliers - Full workflow (Project Manager → Operations → Procurement → CEO → Supplier → Finance)
                                    </small>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Store Selection (Only for Store Requisitions) -->
                        <div class="row mb-4" id="store-field" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="store_id" class="form-label">Project Store <span class="text-danger">*</span></label>
                                    <select name="store_id" id="store_id" class="form-select @error('store_id') is-invalid @enderror">
                                        <option value="">Select Store</option>
                                        @foreach($projectStores as $store)
                                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }} data-inventory-url="{{ route('api.store.inventory', $store->id) }}">
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('store_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4 pt-2">
                                    <div id="store-info" class="alert alert-info" style="display: none;">
                                        <i class="bi bi-info-circle"></i>
                                        <span id="store-message">Select a store to view available inventory items</span>
                                    </div>
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
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" placeholder="Explain the purpose of this requisition and why these items are needed..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Items Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Requisition Items <span class="text-danger">*</span></h6>
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addCustomItem">
                                        <i class="bi bi-plus-circle"></i> Add Custom Item
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="loadStoreItems" style="display: none;">
                                        <i class="bi bi-arrow-clockwise"></i> Reload Store Items
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Store Items Alert -->
                            <div id="store-items-alert" class="alert alert-info" style="display: none;">
                                <i class="bi bi-info-circle"></i>
                                <span id="store-items-message">Items will be loaded automatically when you select a store</span>
                            </div>
                            
                            <div id="items-container">
                                <!-- Items will be loaded here dynamically -->
                            </div>
                            
                            <div class="text-end mt-3">
                                <strong>Estimated Total: UGX <span id="grand-total">0.00</span></strong>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-secondary">Cancel</a>
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

@push('styles')
<style>
.store-item-table {
    max-height: 400px;
    overflow-y: auto;
}
.store-item-row:hover {
    background-color: #f8f9fa;
}
.selected-item-badge {
    font-size: 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
// Use the same JavaScript code from project_manager/requisitions/create.blade.php
// Copy the entire JavaScript section from that file here
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 0;
    let currentStoreId = null;
    
    // Initialize with custom item form
    showCustomItemForm();
    
    // Toggle store field based on requisition type
    const typeSelect = document.getElementById('type');
    const storeField = document.getElementById('store-field');
    const storeItemsAlert = document.getElementById('store-items-alert');
    const loadStoreItemsBtn = document.getElementById('loadStoreItems');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            if (this.value === 'store') {
                storeField.style.display = 'block';
                storeItemsAlert.style.display = 'block';
                loadStoreItemsBtn.style.display = 'inline-block';
                document.getElementById('store_id').required = true;
                
                // Clear items when switching to store type
                clearStoreItems();
            } else {
                storeField.style.display = 'none';
                storeItemsAlert.style.display = 'none';
                loadStoreItemsBtn.style.display = 'none';
                document.getElementById('store_id').required = false;
                
                // Clear store items and show custom item form
                clearStoreItems();
                showCustomItemForm();
            }
        });
        
        // Trigger change on load if there's a previous value
        if (typeSelect.value === 'store') {
            typeSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Load store inventory when store is selected
    document.getElementById('store_id').addEventListener('change', function() {
        const storeId = this.value;
        currentStoreId = storeId;
        
        if (storeId) {
            loadStoreInventory(storeId);
        } else {
            clearStoreItems();
            document.getElementById('store-message').textContent = 'Select a store to view available inventory items';
        }
    });
    
    // Reload store items button
    loadStoreItemsBtn.addEventListener('click', function() {
        if (currentStoreId) {
            loadStoreInventory(currentStoreId);
        }
    });
    
    // Load store inventory function
    function loadStoreInventory(storeId) {
        const storeInfo = document.getElementById('store-info');
        const storeMessage = document.getElementById('store-message');
        const storeItemsMessage = document.getElementById('store-items-message');
        
        storeInfo.style.display = 'block';
        storeMessage.textContent = 'Loading inventory items...';
        storeItemsMessage.textContent = 'Loading inventory items...';
        
        // Show loading state
        document.getElementById('items-container').innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading store inventory...</p></div>';
        
        // Make API call to get store inventory
        fetch(`/api/stores/${storeId}/inventory`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const itemsContainer = document.getElementById('items-container');
                itemsContainer.innerHTML = '';
                
                if (data.items && data.items.length > 0) {
                    storeMessage.textContent = `Found ${data.items.length} available items in store`;
                    storeItemsMessage.textContent = `${data.items.length} items available - Search and select items below`;
                    
                    // Store the data globally
                    window.storeInventoryData = data;
                    
                    // Create search and select interface
                    let storeItemsHTML = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Search for items in store inventory. Select items you need or add custom items.
                        </div>
                        
                        <!-- Search Box -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" id="store-search" class="form-control" placeholder="Search items by name...">
                                    <button class="btn btn-outline-secondary" type="button" id="clear-search">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-selected-items">
                                        <i class="bi bi-plus-circle"></i> Add Selected to Requisition
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="select-all-items">
                                        <i class="bi bi-check-all"></i> Select All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Store Items Table -->
                        <div class="table-responsive store-item-table">
                            <table class="table table-sm table-hover" id="store-items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="select-all-checkbox">
                                        </th>
                                        <th>Item Name</th>
                                        <th>Available</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Quantity to Request</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody id="store-items-tbody">
                    `;

                    data.items.forEach((item, index) => {
                        storeItemsHTML += `
                            <tr class="store-item-row">
                                <td>
                                    <input type="checkbox" class="item-select-checkbox" data-item-index="${index}">
                                </td>
                                <td>
                                    <strong>${item.name}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success">${item.quantity}</span>
                                </td>
                                <td>${item.unit}</td>
                                <td>UGX ${item.unit_price.toLocaleString()}</td>
                                <td width="150">
                                    <input type="number" class="form-control form-control-sm store-item-quantity" 
                                           data-max="${item.quantity}" step="0.01" min="0.01" 
                                           max="${item.quantity}" placeholder="0.00" disabled>
                                    <div class="invalid-feedback">Exceeds available quantity</div>
                                </td>
                                <td width="200">
                                    <input type="text" class="form-control form-control-sm store-item-notes" 
                                           placeholder="Optional notes" disabled>
                                </td>
                            </tr>
                        `;
                    });

                    storeItemsHTML += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    itemsContainer.innerHTML = storeItemsHTML;

                    // Initialize store items functionality
                    initializeStoreItemsFunctionality(data);
                    
                } else {
                    storeMessage.textContent = 'No items available in this store';
                    storeItemsMessage.textContent = 'No items available in store inventory';
                    itemsContainer.innerHTML = `
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle"></i>
                            No items available in this store. Please add custom items below.
                        </div>
                    `;
                    showCustomItemForm();
                }
                
            })
            .catch(error => {
                console.error('Error loading store inventory:', error);
                document.getElementById('items-container').innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i>
                        Failed to load store inventory. Please try again or add custom items.
                    </div>
                `;
                showCustomItemForm();
                storeMessage.textContent = 'Error loading inventory. Please try again.';
                storeItemsMessage.textContent = 'Error loading inventory items';
            });
    }

    // Initialize store items functionality
    function initializeStoreItemsFunctionality(data) {
        // Search functionality
        const searchInput = document.getElementById('store-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('.store-item-row').forEach(row => {
                    const itemName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    row.style.display = itemName.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Clear search
        const clearSearch = document.getElementById('clear-search');
        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                document.getElementById('store-search').value = '';
                document.querySelectorAll('.store-item-row').forEach(row => {
                    row.style.display = '';
                });
            });
        }

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.item-select-checkbox').forEach(checkbox => {
                    checkbox.checked = isChecked;
                    toggleItemInputs(checkbox, isChecked);
                });
            });
        }

        // Individual checkbox functionality
        document.querySelectorAll('.item-select-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleItemInputs(this, this.checked);
            });
        });

        // Add selected items button
        const addSelectedBtn = document.getElementById('add-selected-items');
        if (addSelectedBtn) {
            addSelectedBtn.addEventListener('click', function() {
                addSelectedItemsToRequisition(data);
            });
        }

        // Select all items button
        const selectAllBtn = document.getElementById('select-all-items');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.item-select-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                    toggleItemInputs(checkbox, true);
                });
            });
        }

        // Add quantity validation
        addStoreQuantityValidation();
    }

    // Toggle item inputs based on selection
    function toggleItemInputs(checkbox, isEnabled) {
        const row = checkbox.closest('tr');
        const quantityInput = row.querySelector('.store-item-quantity');
        const notesInput = row.querySelector('.store-item-notes');
        
        quantityInput.disabled = !isEnabled;
        notesInput.disabled = !isEnabled;
        
        if (!isEnabled) {
            quantityInput.value = '';
            notesInput.value = '';
        }
    }

    // Add quantity validation for store items table
    function addStoreQuantityValidation() {
        document.querySelectorAll('.store-item-quantity').forEach(input => {
            input.addEventListener('input', function() {
                const maxQuantity = parseFloat(this.dataset.max);
                const quantity = parseFloat(this.value) || 0;
                
                if (quantity > maxQuantity) {
                    this.classList.add('is-invalid');
                    this.title = `Cannot exceed available quantity (${maxQuantity})`;
                } else {
                    this.classList.remove('is-invalid');
                    this.title = '';
                }
            });
        });
    }

    // Add selected items to requisition
    function addSelectedItemsToRequisition(data) {
        const selectedItems = [];
        
        document.querySelectorAll('.item-select-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.store-item-quantity');
            const notesInput = row.querySelector('.store-item-notes');
            const quantity = quantityInput.value;
            const notes = notesInput.value;
            const itemIndex = parseInt(checkbox.dataset.itemIndex);
            
            if (quantity && parseFloat(quantity) > 0) {
                const item = data.items[itemIndex];
                selectedItems.push({
                    name: item.name,
                    quantity: quantity,
                    unit: item.unit,
                    unit_price: item.unit_price,
                    notes: notes,
                    from_store: true
                });
            }
        });
        
        if (selectedItems.length > 0) {
            // Add items to the main items container
            const itemsContainer = document.getElementById('items-container');
            let itemsHTML = '';
            
            selectedItems.forEach((item, index) => {
                const total = item.quantity * item.unit_price;
                const itemIndex = itemCount + index;
                
                itemsHTML += `
                    <div class="item-row border rounded p-3 mb-3 store-item-added">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Item Name</label>
                                <input type="text" name="items[${itemIndex}][name]" class="form-control" value="${item.name}" readonly required>
                                <input type="hidden" name="items[${itemIndex}][from_store]" value="1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity" 
                                       value="${item.quantity}" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit</label>
                                <input type="text" name="items[${itemIndex}][unit]" class="form-control" value="${item.unit}" readonly required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Unit Price</label>
                                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price" 
                                       value="${item.unit_price}" step="0.01" readonly required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control item-total" value="${total.toFixed(2)}" readonly>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label">Notes (Optional)</label>
                                <input type="text" name="items[${itemIndex}][notes]" class="form-control" value="${item.notes}">
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success me-2 selected-item-badge">From Store</span>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
            });
            
            // Replace the entire items container with the new items
            itemsContainer.innerHTML = itemsHTML;
            
            // Update item count
            itemCount += selectedItems.length;
            
            // Reinitialize calculations
            initializeCalculations();
            
            // Show success message
            alert(`Added ${selectedItems.length} item(s) to requisition`);
            
            // Clear selections after adding
            document.querySelectorAll('.item-select-checkbox:checked').forEach(checkbox => {
                checkbox.checked = false;
                toggleItemInputs(checkbox, false);
            });
            document.getElementById('select-all-checkbox').checked = false;
            
        } else {
            alert('Please select at least one item and enter quantity.');
        }
    }

    // Clear store items
    function clearStoreItems() {
        const itemsContainer = document.getElementById('items-container');
        itemsContainer.innerHTML = '';
        itemCount = 0;
    }
    
    // Show custom item form
    function showCustomItemForm() {
        const itemsContainer = document.getElementById('items-container');
        const customItemRow = `
            <div class="item-row border rounded p-3 mb-3 custom-item">
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
                        <input type="text" name="items[0][unit]" class="form-control" placeholder="e.g., kg, pcs, bags" required>
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
                <button type="button" class="btn btn-outline-danger btn-sm mt-2 remove-item">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        `;
        itemsContainer.innerHTML = customItemRow;
        itemCount = 1;
        initializeCalculations();
    }
    
    // Add custom item
    function addCustomItem() {
        const itemsContainer = document.getElementById('items-container');
        const newRow = document.createElement('div');
        newRow.className = 'item-row border rounded p-3 mb-3 custom-item';
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
    }
    
    // Add custom item button
    document.getElementById('addCustomItem').addEventListener('click', addCustomItem);
    
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
            const fromStoreInput = row.querySelector('input[name*="[from_store]"]');
            
            if (nameInput) nameInput.name = `items[${index}][name]`;
            if (quantityInput) quantityInput.name = `items[${index}][quantity]`;
            if (unitInput) unitInput.name = `items[${index}][unit]`;
            if (unitPriceInput) unitPriceInput.name = `items[${index}][unit_price]`;
            if (notesInput) notesInput.name = `items[${index}][notes]`;
            if (fromStoreInput) fromStoreInput.name = `items[${index}][from_store]`;
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
    
    // Form validation before submission
    document.getElementById('requisitionForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.item-row');
        if (items.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the requisition.');
            return;
        }
        
        // Validate store requisitions have store selected
        const type = document.getElementById('type').value;
        const storeId = document.getElementById('store_id').value;
        
        if (type === 'store' && !storeId) {
            e.preventDefault();
            alert('Please select a store for store requisitions.');
            return;
        }
        
        // Validate all required fields are filled
        let valid = true;
        items.forEach(row => {
            const name = row.querySelector('input[name*="[name]"]');
            const quantity = row.querySelector('input[name*="[quantity]"]');
            const unit = row.querySelector('input[name*="[unit]"]');
            const unitPrice = row.querySelector('input[name*="[unit_price]"]');
            
            if (!name.value || !quantity.value || !unit.value || !unitPrice.value) {
                valid = false;
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required fields for all items.');
        }
    });
    
    // Initialize calculation for first row
    initializeCalculations();
});
</script>
@endpush