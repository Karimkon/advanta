@extends('engineer.layouts.app')

@section('title', 'Create New Requisition')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0"><strong>Create New Requisition</strong></h5>
                        <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Back to Requisitions</span>
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
                            <div class="col-md-6 mb-3 mb-md-0">
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
                                    <small class="text-muted d-block mt-1">
                                        <strong>From Project Store:</strong> Items already in store - Quick approval<br>
                                        <strong>New Purchase:</strong> Need to buy from suppliers - Full workflow
                                    </small>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Store Selection (Only for Store Requisitions) -->
                        <div class="row mb-4" id="store-field" style="display: none;">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="mb-3">
                                    <label for="store_id" class="form-label">Project Store <span class="text-danger">*</span></label>
                                    <select name="store_id" id="store_id" class="form-select @error('store_id') is-invalid @enderror">
                                        <option value="">Select Store</option>
                                        @foreach($projectStores as $store)
                                            <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }} data-inventory-url="{{ route('api.store.inventory', $store->id) }}" data-project-id="{{ $store->project_id }}">
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
                                <div class="mt-md-4 pt-md-2">
                                    <div id="store-info" class="alert alert-info" style="display: none;">
                                        <i class="bi bi-info-circle"></i>
                                        <span id="store-message">Select a store to view available inventory items</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
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
                                    <small class="text-muted">Multiple files allowed (Max: 10MB each)</small>
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

                <!-- Enhanced Product Search Section -->
<div class="card shadow-sm mb-4 product-search-card">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="mb-0 text-dark"><i class="bi bi-cart-plus text-primary"></i> Select Products</h5>
            </div>
            <span class="badge bg-primary" id="product-count-badge">0 items</span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        <!-- Mobile-optimized search layout -->
        <div class="search-section mb-3">
            <div class="row g-2 g-md-3">
                <div class="col-12 col-md-7">
                    <label class="form-label fw-bold small">
                        <i class="bi bi-search"></i> Search Products
                    </label>
                    <div class="search-input-wrapper">
                        <select id="product-search" class="form-select">
                            <option value="">Type to search products...</option>
                        </select>
                    </div>
                </div>
                <div class="col-8 col-md-3">
                    <label class="form-label fw-bold small">
                        <i class="bi bi-funnel"></i> Category
                    </label>
                    <select id="category-filter" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label fw-bold small d-none d-md-block">&nbsp;</label>
                    <label class="form-label fw-bold small d-md-none">Action</label>
                    <button type="button" id="add-selected-product" class="btn btn-success w-100 btn-add-product" disabled>
                        <i class="bi bi-plus-circle"></i> <span class="d-none d-md-inline">Add</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Info banner for engineers -->
        <div class="alert alert-info border-0 shadow-sm mb-3">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-info-circle-fill mt-1"></i>
                <div class="small">
                    <strong>Note:</strong> As an engineer, you only need to specify quantities. Pricing will be handled by Procurement.
                </div>
            </div>
        </div>

        <!-- Selected Products Table - Enhanced for mobile -->
        <div class="table-container" id="products-container" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-muted"><i class="bi bi-list-check"></i> Selected Products</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" id="clear-all-products">
                    <i class="bi bi-trash"></i> Clear All
                </button>
            </div>
            
            <!-- Desktop Table View -->
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-hover align-middle" id="selected-products-table">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th width="150">Quantity</th>
                            <th width="100">Unit</th>
                            <th width="200" colspan="2">Price Info</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="selected-products-tbody">
                        <!-- Products will be added here dynamically -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="d-lg-none" id="mobile-products-list">
                <!-- Product cards will be added here dynamically -->
            </div>

            <div class="text-center text-muted py-3 border-top">
                <small><i class="bi bi-calculator"></i> Total cost will be calculated by Procurement after pricing is determined</small>
            </div>
        </div>

        <!-- Empty state -->
        <div class="empty-state text-center py-5" id="empty-products-state">
            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
            <p class="text-muted mt-3">No products added yet. Search and add products above.</p>
        </div>
    </div>
</div>

                        <!-- Submit Buttons -->
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                            <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-secondary order-2 order-sm-1">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg order-1 order-sm-2">
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
/* Enhanced Product Search Card */
.product-search-card {
    border: 2px solid #e0e0e0;
    overflow: hidden;
}

.product-search-card .card-header {
    background: #fff;
    padding: 1.25rem;
}

.product-search-card .card-header h5 {
    font-weight: 600;
    font-size: 1.25rem;
}

.search-input-wrapper {
    position: relative;
}

.search-input-wrapper::before {
    content: '\F52A';
    font-family: 'bootstrap-icons';
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 10;
    pointer-events: none;
}

.select2-container .select2-selection--single {
    height: 42px !important;
    border: 2px solid #e0e0e0 !important;
    border-radius: 8px !important;
    transition: all 0.3s ease;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
    padding-left: 40px !important;
}

.select2-container--default .select2-selection--single:focus,
.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

/* Mobile optimizations */
@media (max-width: 767.98px) {
    .select2-container {
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    .search-input-wrapper::before {
        left: 10px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 35px !important;
    }
}

/* Button styles */
.btn-add-product {
    height: 42px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-add-product:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.btn-add-product:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Product cards for mobile */
.product-card-mobile {
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    position: relative;
}

.product-card-mobile:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.product-card-mobile .product-header {
    display: flex;
    justify-content: space-between;
    align-items-start;
    margin-bottom: 12px;
}

.product-card-mobile .product-title {
    font-weight: 600;
    font-size: 15px;
    color: #2d3748;
    margin-bottom: 4px;
}

.product-card-mobile .product-category {
    font-size: 12px;
    color: #718096;
}

.product-card-mobile .quantity-section {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    margin-top: 12px;
}

.product-card-mobile .quantity-input {
    flex: 1;
    height: 44px;
    font-size: 16px;
    text-align: center;
    border: 2px solid #dee2e6;
    border-radius: 6px;
}

.product-card-mobile .unit-badge {
    background: #667eea;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}

.product-card-mobile .remove-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
}

/* Empty state */
.empty-state {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Table enhancements */
.table-container {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

#selected-products-table tbody tr {
    transition: all 0.3s ease;
}

#selected-products-table tbody tr:hover {
    background-color: #f8f9ff;
    transform: scale(1.01);
}

/* Badge styling */
#product-count-badge {
    font-size: 14px;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
}

/* Form controls */
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Quantity input styling */
.quantity-input {
    font-weight: 600;
    text-align: center;
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
    opacity: 1;
    height: 30px;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .card-body {
        padding: 1rem !important;
    }
    
    .product-card-mobile {
        padding: 12px;
    }
    
    .btn-lg {
        padding: 12px 20px;
    }
}

/* Store items table styling */
.store-item-table {
    max-height: 400px;
    overflow-y: auto;
}

.store-item-row:hover {
    background-color: #f8f9fa;
}

/* Alert enhancements */
.alert {
    border-radius: 8px;
}

/* Loading state */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Select2 dropdown enhancements */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #667eea !important;
}

.select2-dropdown {
    border: 2px solid #e0e0e0;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.select2-search__field {
    border: 2px solid #e0e0e0 !important;
    border-radius: 6px !important;
    padding: 8px 12px !important;
    font-size: 14px !important;
}

.select2-search__field:focus {
    border-color: #667eea !important;
    outline: none !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCounter = 0;
    let currentStoreId = null;
    let storeInventory = [];

// Filter stores by selected project
$('#project_id').change(function() {
    const selectedProjectId = $(this).val();
    const storeSelect = $('#store_id');
    
    // Show all stores first, then hide non-matching ones
    storeSelect.find('option').each(function() {
        const option = $(this);
        const projectId = option.data('project-id');
        
        if (!option.val()) {
            // Keep the default empty option visible
            option.show();
        } else if (projectId == selectedProjectId) {
            option.show();
        } else {
            option.hide();
        }
    });
    
    // Reset selection if current store doesn't match project
    const currentStore = storeSelect.find('option:selected');
    if (currentStore.val() && currentStore.data('project-id') != selectedProjectId) {
        storeSelect.val('').trigger('change');
    }
});

// Trigger on page load to filter based on initial project selection
if ($('#project_id').val()) {
    $('#project_id').trigger('change');
}


// Replace the current store change handler with this:
$('#store_id').change(function () {
    const storeId = $(this).val();
    const url = $(this).find(':selected').data('inventory-url');
    
    if (!storeId) {
        $('#store-info').hide();
        storeInventory = [];
        return;
    }

    if (!url) {
        $('#store-info').show().addClass('alert-danger');
        $('#store-message').text('Error: No inventory URL found for this store');
        return;
    }

    $('#store-info').show().removeClass('alert-danger').addClass('alert-info');
    $('#store-message').html('<span class="loading-spinner me-2"></span>Loading store inventory...');

    fetch(url)
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        })
        .then(data => {
            storeInventory = data.items || [];
            $('#store-info').removeClass('alert-info alert-danger').addClass('alert-success');
            $('#store-message').text(`Store inventory loaded. ${storeInventory.length} items available.`);
            
            // Clear existing products when store changes
            $('#selected-products-tbody').empty();
            $('#mobile-products-list').empty();
            $('#products-container').hide();
            $('#empty-products-state').show();
            updateProductCount();
        })
        .catch(error => {
            console.error('Error loading store inventory:', error);
            $('#store-info').removeClass('alert-info').addClass('alert-danger');
            $('#store-message').text('Error loading store inventory. Please try again.');
            storeInventory = [];
        });
});

// Enhanced product matching function
function findStoreItemByProduct(product) {
    if (!storeInventory.length) return null;
    
    console.log('Searching for product in store inventory:', { 
        productId: product.id, 
        productName: product.text,
        storeInventoryCount: storeInventory.length 
    });
    
    // Try multiple matching strategies
    let storeItem = null;
    
    // 1. First try exact product_catalog_id match
    storeItem = storeInventory.find(i => i.product_catalog_id == product.id);
    if (storeItem) {
        console.log('Found by product_catalog_id match:', storeItem);
        return storeItem;
    }
    
    // 2. Try name matching (case insensitive)
    storeItem = storeInventory.find(i => 
        i.name?.toLowerCase() === product.text?.toLowerCase() ||
        i.product_name?.toLowerCase() === product.text?.toLowerCase()
    );
    if (storeItem) {
        console.log('Found by name match:', storeItem);
        return storeItem;
    }
    
    // 3. Try partial name matching
    storeItem = storeInventory.find(i => 
        i.name?.toLowerCase().includes(product.text?.toLowerCase()) ||
        product.text?.toLowerCase().includes(i.name?.toLowerCase())
    );
    if (storeItem) {
        console.log('Found by partial name match:', storeItem);
        return storeItem;
    }
    
    console.log('No matching store item found for product:', product);
    return null;
}

    
    console.log('Enhanced Engineer Requisition Form Initialized');
    
    // Initialize Select2 for product search with enhanced config
    $('#product-search').select2({
        placeholder: 'Type to search products...',
        allowClear: true,
        minimumInputLength: 1,
        width: '100%',
        dropdownAutoWidth: true,
        ajax: {
            url: '{{ route("engineer.requisitions.search-products") }}',
            type: 'GET',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    category: $('#category-filter').val()
                };
            },
            processResults: function (data) {
                return {
                    results: data.results || []
                };
            },
            cache: true
        },
        templateResult: formatProduct,
        templateSelection: formatProductSelection
    });

    // Format product in dropdown
    function formatProduct(product) {
        if (product.loading) {
            return product.text;
        }
        
        const $container = $(`
            <div class="d-flex justify-content-between align-items-center py-1">
                <div>
                    <div class="fw-bold">${escapeHtml(product.text)}</div>
                    <small class="text-muted">${escapeHtml(product.category || 'N/A')}</small>
                </div>
                <span class="badge bg-primary">${escapeHtml(product.unit)}</span>
            </div>
        `);
        
        return $container;
    }

    // Format selected product
    function formatProductSelection(product) {
        return product.text || product.id;
    }

    // Category filter change
    $('#category-filter').change(function() {
        $('#product-search').val(null).trigger('change');
    });

    // Enable add button when product is selected
    $('#product-search').on('select2:select', function(e) {
        $('#add-selected-product').prop('disabled', false);
    });

    // Clear add button when selection is cleared
    $('#product-search').on('select2:clear', function() {
        $('#add-selected-product').prop('disabled', true);
    });

    // Add selected product to table
    $('#add-selected-product').click(function() {
        const selectedData = $('#product-search').select2('data');
        
        if (!selectedData || selectedData.length === 0) {
            showNotification('Please select a product first', 'warning');
            return;
        }
        
        const selectedProduct = selectedData[0];
        
        if (selectedProduct && selectedProduct.id) {
            addProductToTable(selectedProduct);
            $('#product-search').val(null).trigger('change');
            $(this).prop('disabled', true);
            updateProductCount();
        } else {
            showNotification('Invalid product selection', 'error');
        }
    });

   function addProductToTable(product) {
    const tbody = $('#selected-products-tbody');
    const mobileList = $('#mobile-products-list');
    const rowId = `product-${productCounter}`;
    const requisitionType = $('#type').val();
    
    // Only check stock for store requisitions, not purchase requisitions
    const storeItem = requisitionType === 'store' ? findStoreItemByProduct(product) : null;
    const availableQty = requisitionType === 'store' && storeItem ? parseFloat(storeItem.quantity) : null;
    
    console.log('Adding product to table:', {
        product: product,
        requisitionType: requisitionType,
        availableQty: availableQty
    });

    // Validate product data
    if (!product.id || !product.text || !product.unit) {
        showNotification('Invalid product data', 'error');
        return;
    }
    
    // Show containers if hidden
    $('#products-container').show();
    $('#empty-products-state').hide();
    
    // For purchase requisitions, don't show available stock or set max quantity
    const stockInfo = requisitionType === 'store' ? 
        `<small class="text-${availableQty > 0 ? 'success' : 'danger'}">Available: ${availableQty}</small>` :
        `<small class="text-info">New purchase - no stock check</small>`;
    
    const maxAttr = requisitionType === 'store' && availableQty ? `max="${availableQty}"` : '';
    
    // Desktop table row
    const row = `
        <tr id="${rowId}" data-product-id="${product.id}">
            <td>
                <strong>${escapeHtml(product.text)}</strong>
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
                       ${maxAttr}
                       required>
                ${stockInfo}
            </td>
            <td><span class="badge bg-secondary">${escapeHtml(product.unit)}</span></td>
            <td colspan="2" class="text-muted">
                <small><i class="bi bi-info-circle"></i> Price to be determined</small>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-product" data-counter="${productCounter}" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    // Mobile card
    const mobileCard = `
        <div class="product-card-mobile" id="mobile-${rowId}" data-product-id="${product.id}">
            <div class="product-header">
                <div style="flex: 1;">
                    <div class="product-title">${escapeHtml(product.text)}</div>
                    <div class="product-category">${escapeHtml(product.category || 'N/A')}</div>
                    ${stockInfo}
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-product" data-counter="${productCounter}" title="Remove">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="quantity-section">
                <input type="number" 
                       class="form-control quantity-input mobile-quantity-${productCounter}" 
                       value="1" 
                       min="0.01" 
                       step="0.01" 
                       ${maxAttr}
                       data-sync="${productCounter}"
                       required>
                <span class="unit-badge">${escapeHtml(product.unit)}</span>
            </div>
            <div class="mt-2 text-muted small">
                <i class="bi bi-info-circle"></i> Price will be determined by Procurement
            </div>
        </div>
    `;
    
    tbody.append(row);
    mobileList.append(mobileCard);
    
    // Sync quantity inputs between desktop and mobile
    $(document).on('input', `input[data-sync="${productCounter}"]`, function() {
        const value = $(this).val();
        $(`input[name="items[${productCounter}][quantity]"]`).val(value);
        $(`.mobile-quantity-${productCounter}`).val(value);
    });
    
    productCounter++;
    updateProductCount();
    
    showNotification('Product added successfully', 'success');
}

// Add this validation function
function validateQuantity(input, availableQty) {
    const value = parseFloat(input.value);
    if (value > availableQty) {
        showNotification(`Cannot request more than ${availableQty} available items`, 'error');
        input.value = availableQty;
        input.focus();
    }
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
        const counter = $(this).data('counter');
        $(`#product-${counter}`).remove();
        $(`#mobile-product-${counter}`).remove();
        
        updateProductCount();
        
        const remainingItems = $('#selected-products-tbody tr').length;
        if (remainingItems === 0) {
            $('#products-container').hide();
            $('#empty-products-state').show();
        }
        
        showNotification('Product removed', 'info');
    });

    // Clear all products
    $('#clear-all-products').click(function() {
        if (confirm('Are you sure you want to remove all products?')) {
            $('#selected-products-tbody').empty();
            $('#mobile-products-list').empty();
            $('#products-container').hide();
            $('#empty-products-state').show();
            updateProductCount();
            showNotification('All products cleared', 'info');
        }
    });

    // Update product count badge
    function updateProductCount() {
        const count = $('#selected-products-tbody tr').length;
        $('#product-count-badge').text(`${count} item${count !== 1 ? 's' : ''}`);
    }

    // Show notification
    function showNotification(message, type = 'info') {
        const iconMap = {
            success: 'check-circle-fill',
            error: 'exclamation-circle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };
        
        const bgMap = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info'
        };
        
        const toast = $(`
            <div class="toast align-items-center text-white bg-${bgMap[type]} border-0 position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${iconMap[type]} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
        bsToast.show();
        
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Toggle store field based on requisition type
    const typeSelect = document.getElementById('type');
    const storeField = document.getElementById('store-field');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const isStore = this.value === 'store';
            storeField.style.display = isStore ? 'block' : 'none';
            document.getElementById('store_id').required = isStore;
        });
        
        // Trigger change on load if there's a previous value
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
    }

    // Form validation before submission
    const requisitionForm = document.getElementById('requisitionForm');
    
if (requisitionForm) {
    requisitionForm.addEventListener('submit', function(e) {
        const items = $('#selected-products-tbody tr');
        const type = document.getElementById('type').value;
        
        if (items.length === 0) {
            e.preventDefault();
            showNotification('Please add at least one product to the requisition', 'error');
            $('html, body').animate({
                scrollTop: $('#products-container').offset().top - 100
            }, 500);
            return false;
        }
        
        // Validate quantities
        let hasInvalidQuantity = false;
        let hasInsufficientStock = false;
        
        items.each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val());
            const maxQty = parseFloat($(this).find('.quantity-input').attr('max')) || Infinity;
            
            if (!qty || qty <= 0) {
                hasInvalidQuantity = true;
                $(this).find('.quantity-input').addClass('is-invalid');
            }
            
            // Stock validation ONLY for store requisitions
            if (type === 'store' && qty > maxQty) {
                hasInsufficientStock = true;
                $(this).find('.quantity-input').addClass('is-invalid');
            }
        });
        
        if (hasInvalidQuantity) {
            e.preventDefault();
            showNotification('Please ensure all quantities are greater than 0', 'error');
            return false;
        }
        
        if (hasInsufficientStock) {
            e.preventDefault();
            showNotification('Some items exceed available stock. Please adjust quantities.', 'error');
            return false;
        }
        
        // Validate store requisitions have store selected
        const storeId = document.getElementById('store_id').value;
        
        if (type === 'store' && !storeId) {
            e.preventDefault();
            showNotification('Please select a store for store requisitions', 'error');
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading-spinner me-2"></span>Creating...');
        
        return true;
    });
}

// Refresh products when requisition type changes
$('#type').change(function() {
    const isStore = $(this).val() === 'store';
    
    // Update all existing products
    $('#selected-products-tbody tr').each(function() {
        const productId = $(this).data('product-id');
        const quantityInput = $(this).find('.quantity-input');
        
        if (isStore) {
            // For store requisitions, check stock and set limits
            const storeItem = findStoreItemByProduct({id: productId, text: $(this).find('strong').text()});
            const availableQty = storeItem ? parseFloat(storeItem.quantity) : 0;
            
            quantityInput.attr('max', availableQty);
            quantityInput.next('small').remove();
            quantityInput.after(`<small class="text-${availableQty > 0 ? 'success' : 'danger'}">Available: ${availableQty}</small>`);
        } else {
            // For purchase requisitions, remove stock limits
            quantityInput.removeAttr('max');
            quantityInput.next('small').remove();
            quantityInput.after('<small class="text-info">New purchase - no stock check</small>');
        }
    });
});

    // Prevent double submission
    $('form').submit(function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    console.log('All event listeners attached and initialized');
});
</script>
@endpush