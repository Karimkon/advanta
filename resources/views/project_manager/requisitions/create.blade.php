@extends('project_manager.layouts.app')

@section('title', 'Create New Requisition')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0"><strong>Create New Requisition</strong></h5>
                        <a href="{{ route('project_manager.requisitions.index') }}" class="btn btn-outline-secondary btn-sm">
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

                    <form action="{{ route('project_manager.requisitions.store') }}" method="POST" enctype="multipart/form-data" id="requisitionForm">
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

                                <!-- Info banner for PM -->
                                <div class="alert alert-info border-0 shadow-sm mb-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-info-circle-fill mt-1"></i>
                                        <div class="small">
                                            <strong>Note:</strong> You can specify quantities and estimated unit prices. Procurement will finalize the actual pricing with suppliers.
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
                                                    <th width="200">Estimated Unit Price (UGX)</th>
                                                    <th width="150">Total</th>
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

                                    <div class="text-end py-3 border-top">
                                        <h5 class="mb-0">Estimated Total: <strong class="text-primary">UGX <span id="grand-total-display">0.00</span></strong></h5>
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
                            <a href="{{ route('project_manager.requisitions.index') }}" class="btn btn-outline-secondary order-2 order-sm-1">
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
    border-radius: 12px;
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
    align-items: start;
    margin-bottom: 12px;
}

.product-card-mobile .product-title {
    font-weight: 600;
    font-size: 15px;
    color: #2d3748;
}

.product-card-mobile .quantity-section, .product-card-mobile .price-section {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
    margin-top: 8px;
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

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

#product-count-badge {
    font-size: 14px;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCounter = 0;
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
            }} else if (projectId == selectedProjectId) {
                option.show();
            }} else {
                option.hide();
            }}
        }});

        // Reset selection if current store doesn't match project
        const currentStore = storeSelect.find('option:selected');
        if (currentStore.val() && currentStore.data('project-id') != selectedProjectId) {
            storeSelect.val('').trigger('change');
        }}
    }});

    // Trigger on page load to filter based on initial project selection
    if ($('#project_id').val()) {
        $('#project_id').trigger('change');
    }}

    // Initialize Select2 for product search
    $('#product-search').select2({
        placeholder: 'Type to search products...',
        allowClear: true,
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: '{{ route("project_manager.requisitions.search-products") }}',
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
                return { results: data.results || [] };
            },
            cache: true
        },
        templateResult: formatProduct,
        templateSelection: (product) => product.text || product.id
    });

    function formatProduct(product) {
        if (product.loading) return product.text;
        return $(`
            <div class="d-flex justify-content-between align-items-center py-1">
                <div>
                    <div class="fw-bold">${escapeHtml(product.text)}</div>
                    <small class="text-muted">${escapeHtml(product.category || 'N/A')}</small>
                </div>
                <span class="badge bg-primary">${escapeHtml(product.unit)}</span>
            </div>
        `);
    }

    $('#product-search').on('select2:select', () => $('#add-selected-product').prop('disabled', false));
    $('#product-search').on('select2:clear', () => $('#add-selected-product').prop('disabled', true));

    $('#add-selected-product').click(function() {
        const selectedData = $('#product-search').select2('data');
        if (!selectedData || selectedData.length === 0) return;
        
        addProductToTable(selectedData[0]);
        $('#product-search').val(null).trigger('change');
        $(this).prop('disabled', true);
        calculateGrandTotal();
    });

    function addProductToTable(product) {
        const rowId = `product-${productCounter}`;
        const requisitionType = $('#type').val();
        
        const storeItem = requisitionType === 'store' ? findStoreItemByProduct(product) : null;
        const availableQty = requisitionType === 'store' && storeItem ? parseFloat(storeItem.quantity) : null;
        
        $('#products-container').show();
        $('#empty-products-state').hide();
        
        const stockInfo = requisitionType === 'store' ? 
            `<small class="text-${availableQty > 0 ? 'success' : 'danger'}">Available: ${availableQty}</small>` :
            `<small class="text-info">New purchase</small>`;
        
        const maxAttr = requisitionType === 'store' && availableQty ? `max="${availableQty}"` : '';

        // Desktop Row
        const row = `
            <tr id="${rowId}" data-product-id="${product.id}">
                <td>
                    <strong>${escapeHtml(product.text)}</strong>
                    <input type="hidden" name="items[${productCounter}][product_catalog_id]" value="${product.id}">
                    <input type="hidden" name="items[${productCounter}][name]" value="${escapeHtml(product.text)}">
                    <input type="hidden" name="items[${productCounter}][unit]" value="${escapeHtml(product.unit)}">
                    <br><small class="text-muted">${escapeHtml(product.category || 'N/A')}</small>
                </td>
                <td>
                    <input type="number" name="items[${productCounter}][quantity]" class="form-control form-control-sm quantity-input" 
                           value="1" min="0.01" step="0.01" ${maxAttr} data-sync="${productCounter}" required>
                    ${stockInfo}
                </td>
                <td><span class="badge bg-secondary">${escapeHtml(product.unit)}</span></td>
                <td>
                    <input type="number" name="items[${productCounter}][unit_price]" class="form-control form-control-sm price-input" 
                           value="${storeItem ? storeItem.unit_price : 0}" min="0" step="0.01" data-sync-price="${productCounter}" required>
                </td>
                <td><span class="item-total-display" id="total-${productCounter}">0.00</span></td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-product" data-counter="${productCounter}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        // Mobile Card
        const mobileCard = `
            <div class="product-card-mobile" id="mobile-${rowId}" data-product-id="${product.id}">
                <div class="product-header">
                    <div style="flex: 1;">
                        <div class="product-title">${escapeHtml(product.text)}</div>
                        <div class="product-category">${escapeHtml(product.category || 'N/A')}</div>
                        ${stockInfo}
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-product" data-counter="${productCounter}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="quantity-section">
                    <input type="number" class="form-control quantity-input" value="1" min="0.01" step="0.01" ${maxAttr} data-sync="${productCounter}" required>
                    <span class="unit-badge">${escapeHtml(product.unit)}</span>
                </div>
                <div class="price-section mt-2">
                    <span class="me-2 small fw-bold">Price:</span>
                    <input type="number" class="form-control form-control-sm price-input" value="${storeItem ? storeItem.unit_price : 0}" 
                           min="0" step="0.01" data-sync-price="${productCounter}" required>
                </div>
            </div>
        `;

        $('#selected-products-tbody').append(row);
        $('#mobile-products-list').append(mobileCard);
        
        productCounter++;
        updateProductCount();
        calculateGrandTotal();
    }

    $(document).on('input', '.quantity-input, .price-input', function() {
        const counter = $(this).data('sync') || $(this).data('sync-price');
        const qty = parseFloat($(`.quantity-input[data-sync="${counter}"]`).val()) || 0;
        const price = parseFloat($(`.price-input[data-sync-price="${counter}"]`).val()) || 0;
        
        // Sync values
        if ($(this).hasClass('quantity-input')) {
            $(`.quantity-input[data-sync="${counter}"]`).val($(this).val());
        } else {
            $(`.price-input[data-sync-price="${counter}"]`).val($(this).val());
        }
        
        $(`#total-${counter}`).text((qty * price).toLocaleString(undefined, {minimumFractionDigits: 2}));
        calculateGrandTotal();
    });

    function calculateGrandTotal() {
        let grandTotal = 0;
        $('#selected-products-tbody tr').each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            grandTotal += (qty * price);
        });
        $('#grand-total-display').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    $(document).on('click', '.remove-product', function() {
        const counter = $(this).data('counter');
        $(`#product-${counter}`).remove();
        $(`#mobile-product-${counter}`).remove();
        updateProductCount();
        calculateGrandTotal();
        if ($('#selected-products-tbody tr').length === 0) {
            $('#products-container').hide();
            $('#empty-products-state').show();
        }
    });

    function updateProductCount() {
        const count = $('#selected-products-tbody tr').length;
        $('#product-count-badge').text(`${count} item${count !== 1 ? 's' : ''}`);
    }

    $('#store_id').change(function() {
        const storeId = $(this).val();
        const url = $(this).find(':selected').data('inventory-url');
        if (!storeId) { $('#store-info').hide(); storeInventory = []; return; }
        
        $('#store-info').show().html('<span class="loading-spinner me-2"></span>Loading inventory...');
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                storeInventory = data.items || [];
                $('#store-info').html(`<i class="bi bi-check-circle text-success"></i> ${storeInventory.length} items loaded.`);
            });
    });

    function findStoreItemByProduct(product) {
        return storeInventory.find(i => i.product_catalog_id == product.id || i.name?.toLowerCase() === product.text?.toLowerCase());
    }

    function escapeHtml(text) {
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    $('#type').change(function() {
        const isStore = $(this).val() === 'store';
        $('#store-field').toggle(isStore);
        $('#store_id').prop('required', isStore);
    });

    $('#requisitionForm').on('submit', function(e) {
        if ($('#selected-products-tbody tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one product.');
            return false;
        }
    });
});
</script>
@endpush