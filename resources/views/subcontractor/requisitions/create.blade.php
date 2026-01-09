@extends('subcontractor.layouts.app')

@section('title', 'Create Requisition')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create Requisition</h2>
            <p class="text-muted mb-0">Request materials or purchases for your project work</p>
        </div>
        <a href="{{ route('subcontractor.requisitions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Error Display -->
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

    <form action="{{ route('subcontractor.requisitions.store') }}" method="POST" id="requisitionForm">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle text-primary me-2"></i>Requisition Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">Select Project</option>
                                    @foreach($activeContracts as $contract)
                                        @php
                                            $projectStore = $contract->project->stores->where('type', 'project')->first();
                                        @endphp
                                        <option value="{{ $contract->project_id }}"
                                                {{ old('project_id') == $contract->project_id ? 'selected' : '' }}
                                                data-store-id="{{ $projectStore?->id }}"
                                                data-store-name="{{ $projectStore?->name ?? 'Project Store' }}">
                                            {{ $contract->project->name }} - {{ $contract->work_description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Requisition Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="store" {{ old('type', request('type')) === 'store' ? 'selected' : '' }}>Store Requisition (From Project Store)</option>
                                    <option value="purchase" {{ old('type', request('type')) === 'purchase' ? 'selected' : '' }}>Purchase Requisition (New Purchase)</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <strong>Store:</strong> Request from existing project inventory |
                                    <strong>Purchase:</strong> Request new items to be purchased
                                </small>
                            </div>

                            <!-- Store Selection (Only for Store Requisitions) -->
                            <div class="col-md-6 mb-3" id="store-field" style="display: none;">
                                <label for="store_id" class="form-label">Project Store <span class="text-danger">*</span></label>
                                <select name="store_id" id="store_id" class="form-select">
                                    <option value="">Select Store</option>
                                </select>
                                <div id="store-info" class="alert alert-info mt-2" style="display: none;">
                                    <small><i class="bi bi-info-circle"></i> <span id="store-message">Select a store to view available inventory</span></small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="urgency" class="form-label">Urgency <span class="text-danger">*</span></label>
                                <select name="urgency" id="urgency" class="form-select @error('urgency') is-invalid @enderror" required>
                                    <option value="low" {{ old('urgency') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('urgency', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('urgency') === 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('urgency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="reason" class="form-label">Reason / Justification <span class="text-danger">*</span></label>
                                <textarea name="reason" id="reason" rows="3"
                                          class="form-control @error('reason') is-invalid @enderror"
                                          placeholder="Explain why you need these items..."
                                          required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Search Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-cart-plus text-primary me-2"></i>Select Products</h5>
                        <span class="badge bg-primary" id="product-count-badge">0 items</span>
                    </div>
                    <div class="card-body">
                        <!-- Search Section -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-bold small"><i class="bi bi-search"></i> Search Products</label>
                                <select id="product-search" class="form-select" style="width: 100%;">
                                    <option value="">Type to search products...</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small"><i class="bi bi-funnel"></i> Category</label>
                                <select id="category-filter" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small">&nbsp;</label>
                                <button type="button" id="add-selected-product" class="btn btn-success w-100" disabled>
                                    <i class="bi bi-plus-circle"></i> Add
                                </button>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-info border-0 shadow-sm mb-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle-fill mt-1"></i>
                                <div class="small">
                                    <strong>Note:</strong> For store requisitions, only products available in the store inventory are shown. For purchase requisitions, you can search all products from the catalog.
                                </div>
                            </div>
                        </div>

                        <!-- Selected Products Table -->
                        <div id="products-container" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 text-muted"><i class="bi bi-list-check"></i> Selected Products</h6>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="clear-all-products">
                                    <i class="bi bi-trash"></i> Clear All
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="selected-products-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th width="150">Quantity</th>
                                            <th width="100">Unit</th>
                                            <th width="150">Est. Price</th>
                                            <th width="80">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected-products-tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div class="text-center py-5" id="empty-products-state">
                            <i class="bi bi-inbox display-1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3">No products added yet. Search and add products above.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items:</span>
                            <strong id="totalItems">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Estimated Total:</span>
                            <strong id="estimatedTotal">UGX 0</strong>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-send me-1"></i> Submit Requisition
                        </button>
                    </div>
                </div>

                <!-- Workflow Info -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Approval Workflow</h6>
                    </div>
                    <div class="card-body">
                        <div class="workflow-info">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning me-2">1</span>
                                <small>You submit requisition</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info me-2">2</span>
                                <small>Project Manager reviews</small>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary me-2">3</span>
                                <small>Operations/Procurement processes</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">4</span>
                                <small>Delivery & Completion</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
.select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.375rem !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
.product-row {
    transition: all 0.3s ease;
}
.product-row:hover {
    background-color: #f8f9fa;
}
</style>

@push('scripts')
<!-- Include Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCounter = 0;
    let storeInventory = [];
    let currentStoreId = null;
    let searchResultsCache = {}; // Cache search results to preserve all fields

    // Initialize Select2 for product search
    $('#product-search').select2({
        theme: 'bootstrap-5',
        placeholder: 'Type to search products...',
        allowClear: true,
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: '{{ route("subcontractor.requisitions.search-products") }}',
            type: 'GET',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                const data = {
                    q: params.term,
                    category: $('#category-filter').val()
                };
                // For store requisitions, only search within store inventory
                if ($('#type').val() === 'store' && currentStoreId) {
                    data.store_id = currentStoreId;
                }
                return data;
            },
            processResults: function(data) {
                // Cache results to preserve all custom fields
                (data.results || []).forEach(item => {
                    searchResultsCache[item.id] = item;
                });
                return { results: data.results || [] };
            },
            cache: true
        },
        templateResult: formatProduct,
        templateSelection: formatProductSelection
    });

    // Format product in dropdown
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

    function formatProductSelection(product) {
        return product.text || product.id;
    }

    // Enable add button when product selected
    $('#product-search').on('select2:select', function() {
        $('#add-selected-product').prop('disabled', false);
    });

    $('#product-search').on('select2:clear', function() {
        $('#add-selected-product').prop('disabled', true);
    });

    // Toggle store field based on type
    $('#type').change(function() {
        const isStore = $(this).val() === 'store';
        $('#store-field').toggle(isStore);
        $('#store_id').prop('required', isStore);

        if (isStore) {
            loadProjectStores();
        } else {
            storeInventory = [];
            $('#store-info').hide();
        }

        // Clear products when type changes
        clearAllProducts();
    });

    // Load stores for selected project
    function loadProjectStores() {
        const projectId = $('#project_id').val();
        const storeSelect = $('#store_id');

        if (!projectId) {
            storeSelect.html('<option value="">Select Project First</option>');
            return;
        }

        // Get store ID and name from selected project option
        const storeId = $('#project_id option:selected').data('store-id');
        const storeName = $('#project_id option:selected').data('store-name') || 'Project Store';

        if (storeId) {
            storeSelect.html(`<option value="${storeId}" selected>${escapeHtml(storeName)}</option>`);
            loadStoreInventory(storeId);
        } else {
            storeSelect.html('<option value="">No store found for this project</option>');
        }
    }

    // Load store inventory
    function loadStoreInventory(storeId) {
        currentStoreId = storeId;
        $('#store-info').show();
        $('#store-message').html('<span class="spinner-border spinner-border-sm me-1"></span>Loading inventory...');

        fetch(`/api/stores/${storeId}/inventory`)
            .then(res => res.json())
            .then(data => {
                storeInventory = data.items || [];
                $('#store-info').removeClass('alert-danger').addClass('alert-success');
                $('#store-message').text(`${storeInventory.length} items available in store`);
            })
            .catch(error => {
                console.error('Error loading inventory:', error);
                $('#store-info').removeClass('alert-success').addClass('alert-danger');
                $('#store-message').text('Failed to load store inventory');
                storeInventory = [];
            });
    }

    // Project change handler
    $('#project_id').change(function() {
        if ($('#type').val() === 'store') {
            loadProjectStores();
        }
        clearAllProducts();
    });

    // Store change handler
    $('#store_id').change(function() {
        const storeId = $(this).val();
        if (storeId) {
            loadStoreInventory(storeId);
        }
        clearAllProducts();
    });

    // Add product to table
    $('#add-selected-product').click(function() {
        const selectedData = $('#product-search').select2('data');

        if (!selectedData || selectedData.length === 0) {
            showToast('Please select a product first', 'warning');
            return;
        }

        const product = selectedData[0];
        if (product && product.id) {
            // Get full product data from cache (Select2 only preserves id and text)
            const fullProduct = searchResultsCache[product.id] || product;
            addProductToTable(fullProduct);
            $('#product-search').val(null).trigger('change');
            $(this).prop('disabled', true);
        }
    });

    function addProductToTable(product) {
        const tbody = $('#selected-products-tbody');
        const rowId = `product-${productCounter}`;
        const type = $('#type').val();

        // Check store inventory for store requisitions
        let availableQty = null;
        let stockInfo = '';
        let maxAttr = '';

        if (type === 'store') {
            // Use available_stock from search result if present, otherwise lookup in storeInventory
            if (product.available_stock !== undefined) {
                availableQty = parseFloat(product.available_stock);
            } else {
                const storeItem = findStoreItem(product);
                availableQty = storeItem ? parseFloat(storeItem.quantity) : 0;
            }
            stockInfo = `<small class="text-${availableQty > 0 ? 'success' : 'danger'}">Available: ${availableQty}</small>`;
            maxAttr = availableQty > 0 ? `max="${availableQty}"` : '';

            if (availableQty <= 0) {
                showToast('This item is out of stock in the store', 'warning');
            }
        } else {
            stockInfo = '<small class="text-info">New purchase</small>';
        }

        const unitPrice = product.price || 0;

        const row = `
            <tr id="${rowId}" class="product-row" data-product-id="${product.id}">
                <td>
                    <strong>${escapeHtml(product.text)}</strong>
                    <input type="hidden" name="items[${productCounter}][product_catalog_id]" value="${product.product_catalog_id || ''}">
                    <input type="hidden" name="items[${productCounter}][name]" value="${escapeHtml(product.text)}">
                    <input type="hidden" name="items[${productCounter}][unit]" value="${escapeHtml(product.unit)}">
                    <input type="hidden" name="items[${productCounter}][estimated_unit_price]" value="${unitPrice}">
                    <br><small class="text-muted">${escapeHtml(product.category || 'N/A')}</small>
                </td>
                <td>
                    <input type="number"
                           name="items[${productCounter}][quantity]"
                           class="form-control form-control-sm quantity-input"
                           value="1"
                           min="0.01"
                           step="0.01"
                           ${maxAttr}
                           data-price="${unitPrice}"
                           required>
                    ${stockInfo}
                </td>
                <td><span class="badge bg-secondary">${escapeHtml(product.unit)}</span></td>
                <td class="item-subtotal">UGX ${numberFormat(unitPrice)}</td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-product" data-row="${rowId}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.append(row);

        // Show container
        $('#products-container').show();
        $('#empty-products-state').hide();

        productCounter++;
        updateTotals();
        showToast('Product added', 'success');
    }

    function findStoreItem(product) {
        if (!storeInventory.length) return null;

        // Try matching by inventory_item_id first (for store search results)
        if (product.inventory_item_id) {
            let item = storeInventory.find(i => i.id == product.inventory_item_id);
            if (item) return item;
        }

        // Try matching by product_catalog_id
        if (product.product_catalog_id) {
            let item = storeInventory.find(i => i.product_catalog_id == product.product_catalog_id);
            if (item) return item;
        }

        // Try matching by inventory_item_id
        let item = storeInventory.find(i => i.id == product.inventory_item_id);
        if (item) return item;

        // Try matching by inventory item id directly
        item = storeInventory.find(i => i.id == product.id);
        if (item) return item;

        // Try matching by name
        item = storeInventory.find(i =>
            i.name?.toLowerCase() === product.text?.toLowerCase() ||
            i.product_name?.toLowerCase() === product.text?.toLowerCase()
        );
        return item;
    }

    // Remove product
    $(document).on('click', '.remove-product', function() {
        const rowId = $(this).data('row');
        $(`#${rowId}`).remove();
        updateTotals();

        if ($('#selected-products-tbody tr').length === 0) {
            $('#products-container').hide();
            $('#empty-products-state').show();
        }

        showToast('Product removed', 'info');
    });

    // Quantity change handler
    $(document).on('input', '.quantity-input', function() {
        updateTotals();
    });

    // Clear all products
    $('#clear-all-products').click(function() {
        if (confirm('Remove all products?')) {
            clearAllProducts();
        }
    });

    function clearAllProducts() {
        $('#selected-products-tbody').empty();
        $('#products-container').hide();
        $('#empty-products-state').show();
        updateTotals();
    }

    function updateTotals() {
        let total = 0;
        let count = 0;

        $('#selected-products-tbody tr').each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.quantity-input').data('price')) || 0;
            const subtotal = qty * price;

            $(this).find('.item-subtotal').text('UGX ' + numberFormat(subtotal));
            total += subtotal;
            count++;
        });

        $('#totalItems').text(count);
        $('#estimatedTotal').text('UGX ' + numberFormat(total));
        $('#product-count-badge').text(count + ' item' + (count !== 1 ? 's' : ''));
    }

    function numberFormat(num) {
        return Math.round(num).toLocaleString();
    }

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text || '').replace(/[&<>"']/g, m => map[m]);
    }

    function showToast(message, type) {
        const bgClass = type === 'success' ? 'bg-success' : type === 'warning' ? 'bg-warning' : type === 'error' ? 'bg-danger' : 'bg-info';
        const toast = $(`
            <div class="toast align-items-center text-white ${bgClass} border-0 position-fixed top-0 end-0 m-3" style="z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        $('body').append(toast);
        new bootstrap.Toast(toast[0], { delay: 2000 }).show();
        toast.on('hidden.bs.toast', () => toast.remove());
    }

    // Form validation
    $('#requisitionForm').submit(function(e) {
        const items = $('#selected-products-tbody tr');
        const type = $('#type').val();

        if (items.length === 0) {
            e.preventDefault();
            showToast('Please add at least one product', 'error');
            return false;
        }

        // Validate store requisition has store selected
        if (type === 'store' && !$('#store_id').val()) {
            e.preventDefault();
            showToast('Please select a store', 'error');
            return false;
        }

        // Validate quantities
        let valid = true;
        items.each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val());
            const max = parseFloat($(this).find('.quantity-input').attr('max'));

            if (!qty || qty <= 0) {
                valid = false;
                $(this).find('.quantity-input').addClass('is-invalid');
            }

            if (type === 'store' && max && qty > max) {
                valid = false;
                $(this).find('.quantity-input').addClass('is-invalid');
                showToast('Some quantities exceed available stock', 'error');
            }
        });

        if (!valid) {
            e.preventDefault();
            return false;
        }

        // Disable submit button
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');
    });

    // Trigger type change on load if preselected
    if ($('#type').val()) {
        $('#type').trigger('change');
    }
});
</script>
@endpush
@endsection
