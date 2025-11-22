@extends('engineer.layouts.app')

@section('title', 'Create New Requisition')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><strong>Create New Requisition</strong></h5>
                        <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Requisitions
                        </a>
                    </div>
                </div>
                <div class="card-body">
                     <!-- ERROR DISPLAY -->
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
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
                            
                            <!-- Add this debug line temporarily -->
<div style="display: none;">
    Route URL: {{ route('engineer.requisitions.search-products') }}
</div>

<script>
console.log('Route URL:', '{{ route("engineer.requisitions.search-products") }}');
</script>
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

                <!-- Product Search Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Select Products</h5>
        <small class="text-muted">As an engineer, you only need to specify quantities. Pricing will be handled by Procurement.</small>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Search Products</label>
                <select id="product-search" class="form-select">
                    <option value="">Type to search products...</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter by Category</label>
                <select id="category-filter" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="button" id="add-selected-product" class="btn btn-primary w-100" disabled>
                    <i class="bi bi-plus-circle"></i> Add
                </button>
            </div>
        </div>

        <!-- Selected Products Table -->
        <div class="table-responsive">
            <table class="table table-sm" id="selected-products-table" style="display: none;">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th width="150">Quantity</th>
                        <th width="100">Unit</th>
                        <th width="200" colspan="2">Price Info</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody id="selected-products-tbody">
                    <!-- Products will be added here dynamically -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-muted text-center">
                            <small><i class="bi bi-info-circle"></i> Total cost will be calculated by Procurement after pricing is determined</small>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Hidden form fields for submission -->
<div id="form-fields-container" style="display: none;">
    <!-- Dynamic form fields will be added here -->
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

/* Color-coded item borders */
.item-row {
    border-width: 3px !important;
    border-style: solid !important;
    transition: all 0.3s ease;
}

.custom-item {
    border-color: #0d6efd !important; /* Blue for custom items */
    background-color: #f8f9ff;
}

.store-item-added {
    border-color: #198754 !important; /* Green for store items */
    background-color: #f8fff9;
}

.new-item-highlight {
    animation: pulse-highlight 2s ease-in-out;
    border-color: #ffc107 !important; /* Yellow for newly added items */
    background-color: #fffbf0;
}

@keyframes pulse-highlight {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

.item-type-indicator {
    position: absolute;
    top: -8px;
    left: 15px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: bold;
    color: white;
}

.custom-item .item-type-indicator {
    background: #0d6efd;
    content: "CUSTOM ITEM";
}

.store-item-added .item-type-indicator {
    background: #198754;
    content: "FROM STORE";
}

.item-row {
    position: relative;
    padding-top: 15px !important;
}

.item-row::before {
    content: attr(data-item-type);
    position: absolute;
    top: -8px;
    left: 15px;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: bold;
    color: white;
    z-index: 10;
}

.custom-item::before {
    content: "CUSTOM ITEM";
    background: #0d6efd;
}

.store-item-added::before {
    content: "FROM STORE";
    background: #198754;
}

/* Store items table styling */
.store-item-selected {
    background-color: #e8f5e8 !important;
    border-left: 4px solid #198754;
}

.store-item-quantity:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.store-item-notes:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCounter = 0;
    let currentStoreId = null;
    
    console.log('Engineer Requisition Form Initialized');
    
    // Initialize Select2 for product search
    $('#product-search').select2({
        placeholder: 'Type to search products...',
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("engineer.requisitions.search-products") }}',
            type: 'GET',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                console.log('Searching for:', params.term);
                return {
                    q: params.term,
                    category: $('#category-filter').val()
                };
            },
            processResults: function (data) {
                console.log('Search results received:', data);
                if (!data.results || data.results.length === 0) {
                    console.warn('No products found');
                }
                return {
                    results: data.results || []
                };
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                console.error('Response:', xhr.responseText);
            },
            cache: true
        }
    });

    // Category filter change
    $('#category-filter').change(function() {
        console.log('Category changed:', $(this).val());
        $('#product-search').val(null).trigger('change');
    });

    // Enable add button when product is selected
    $('#product-search').on('select2:select', function(e) {
        console.log('Product selected:', e.params.data);
        $('#add-selected-product').prop('disabled', false);
    });

    // Clear add button when selection is cleared
    $('#product-search').on('select2:clear', function() {
        console.log('Selection cleared');
        $('#add-selected-product').prop('disabled', true);
    });

    // Add selected product to table
    $('#add-selected-product').click(function() {
        console.log('Add button clicked');
        const selectedData = $('#product-search').select2('data');
        
        if (!selectedData || selectedData.length === 0) {
            console.error('No product selected');
            alert('Please select a product first');
            return;
        }
        
        const selectedProduct = selectedData[0];
        console.log('Adding product:', selectedProduct);
        
        if (selectedProduct && selectedProduct.id) {
            addProductToTable(selectedProduct);
            $('#product-search').val(null).trigger('change');
            $(this).prop('disabled', true);
        } else {
            console.error('Invalid product data:', selectedProduct);
            alert('Invalid product selection');
        }
    });

    function addProductToTable(product) {
        console.log('addProductToTable called with:', product);
        
        const tbody = $('#selected-products-tbody');
        const table = $('#selected-products-table');
        const rowId = `product-${productCounter}`;
        
        // Validate product data
        if (!product.id || !product.text || !product.unit) {
            console.error('Invalid product data:', product);
            alert('Invalid product data');
            return;
        }
        
        // Show table if hidden
        if (tbody.children().length === 0) {
            console.log('Showing products table');
            table.show();
        }
        
        // FIXED: Include ALL required fields for validation
        const row = `
            <tr id="${rowId}" data-product-id="${product.id}">
                <td>
                    <strong>${escapeHtml(product.text)}</strong>
                    <!-- Hidden fields for submission -->
                    <input type="hidden" name="items[${productCounter}][product_catalog_id]" value="${product.id}">
                    <input type="hidden" name="items[${productCounter}][name]" value="${escapeHtml(product.text)}">
                    <input type="hidden" name="items[${productCounter}][unit]" value="${escapeHtml(product.unit)}">
                    <input type="hidden" name="items[${productCounter}][unit_price]" value="0">
                    <br>
                    <small class="text-muted">${escapeHtml(product.category || 'N/A')}</small>
                </td>
                <td>
                    <input type="number" 
                           name="items[${productCounter}][quantity]" 
                           class="form-control form-control-sm quantity-input" 
                           value="1" 
                           min="0.01" 
                           step="0.01" 
                           required>
                </td>
                <td>${escapeHtml(product.unit)}</td>
                <td colspan="2" class="text-muted">
                    <small><i class="bi bi-info-circle"></i> Price will be determined by Procurement</small>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-product" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        tbody.append(row);
        productCounter++;
        
        console.log('Product added. Total products:', tbody.children().length);
        console.log('Product counter:', productCounter);
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    // Remove product
    $(document).on('click', '.remove-product', function() {
        console.log('Removing product');
        $(this).closest('tr').remove();
        
        const remainingItems = $('#selected-products-tbody tr').length;
        console.log('Remaining items:', remainingItems);
        
        // Hide table if no products
        if (remainingItems === 0) {
            console.log('Hiding products table');
            $('#selected-products-table').hide();
        }
    });

    // Toggle store field based on requisition type
    const typeSelect = document.getElementById('type');
    const storeField = document.getElementById('store-field');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const isStore = this.value === 'store';
            console.log('Type changed:', this.value, 'isStore:', isStore);
            storeField.style.display = isStore ? 'block' : 'none';
            document.getElementById('store_id').required = isStore;
        });
        
        // Trigger change on load if there's a previous value
        if (typeSelect.value) {
            console.log('Triggering type change on load');
            typeSelect.dispatchEvent(new Event('change'));
        }
    }

    // Form validation before submission
    const requisitionForm = document.getElementById('requisitionForm');
    
    if (requisitionForm) {
        requisitionForm.addEventListener('submit', function(e) {
            console.log('Form submission started');
            
            const items = $('#selected-products-tbody tr');
            console.log('Items in table:', items.length);
            
            if (items.length === 0) {
                e.preventDefault();
                console.error('No items in requisition');
                alert('Please add at least one product to the requisition.');
                return false;
            }
            
            // Validate quantities
            let hasInvalidQuantity = false;
            items.each(function() {
                const qty = parseFloat($(this).find('.quantity-input').val());
                if (!qty || qty <= 0) {
                    hasInvalidQuantity = true;
                }
            });
            
            if (hasInvalidQuantity) {
                e.preventDefault();
                console.error('Invalid quantities detected');
                alert('Please ensure all quantities are greater than 0.');
                return false;
            }
            
            // Validate store requisitions have store selected
            const type = document.getElementById('type').value;
            const storeId = document.getElementById('store_id').value;
            
            console.log('Type:', type, 'Store ID:', storeId);
            
            if (type === 'store' && !storeId) {
                e.preventDefault();
                console.error('No store selected for store requisition');
                alert('Please select a store for store requisitions.');
                return false;
            }
            
            // Log form data for debugging
            const formData = new FormData(requisitionForm);
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value);
            }
            
            console.log('Form validation passed - submitting');
            return true;
        });
    }

    console.log('All event listeners attached');
});
</script>
@endpush