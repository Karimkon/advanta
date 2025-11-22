@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><strong>Edit Product</strong></h5>
                        <a href="{{ route('admin.product-catalog.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Catalog
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.product-catalog.update', $productCatalog) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $productCatalog->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU Code</label>
                                    <input type="text" name="sku" id="sku" 
                                           class="form-control @error('sku') is-invalid @enderror" 
                                           value="{{ old('sku', $productCatalog->sku) }}">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" 
                                            class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" 
                                                {{ old('category', $productCatalog->category) == $category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                    <input type="text" name="unit" id="unit" 
                                           class="form-control @error('unit') is-invalid @enderror" 
                                           value="{{ old('unit', $productCatalog->unit) }}" required>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $productCatalog->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="specifications" class="form-label">Specifications</label>
                            <textarea name="specifications" id="specifications" 
                                      class="form-control @error('specifications') is-invalid @enderror" 
                                      rows="3">{{ old('specifications', $productCatalog->specifications) }}</textarea>
                            @error('specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                       value="1" {{ old('is_active', $productCatalog->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Product
                                </label>
                            </div>
                            <small class="text-muted">Inactive products won't appear in selection lists</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.product-catalog.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update Product
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><strong>Product Usage</strong></h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-3">
                                <h4 class="text-primary">{{ $productCatalog->requisition_items_count }}</h4>
                                <p class="text-muted mb-0">Requisition Usage</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3">
                                <h4 class="text-success">{{ $productCatalog->inventory_items_count }}</h4>
                                <p class="text-muted mb-0">Inventory Usage</p>
                            </div>
                        </div>
                    </div>
                    
                    @if(!$productCatalog->canBeDeleted())
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            This product cannot be deleted because it's being used in 
                            {{ $productCatalog->requisition_items_count }} requisitions and 
                            {{ $productCatalog->inventory_items_count }} inventory items.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection