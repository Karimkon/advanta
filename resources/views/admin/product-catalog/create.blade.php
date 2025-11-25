@extends('admin.layouts.app')

@section('title', 'Add Product to Catalog')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><strong>Add New Product</strong></h5>
                        <a href="{{ route('admin.product-catalog.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Catalog
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.product-catalog.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
                                           placeholder="Enter product name" required>
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
                                           value="{{ old('sku') }}" 
                                           placeholder="e.g., PROD-001">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" 
                                            class="form-select @error('category_id') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                    <input type="text" name="unit" id="unit" 
                                           class="form-control @error('unit') is-invalid @enderror" 
                                           value="{{ old('unit') }}" 
                                           placeholder="e.g., pcs, kg, bags, meters" required>
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Standard unit of measurement for this product</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Brief description of the product">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="specifications" class="form-label">Specifications</label>
                            <textarea name="specifications" id="specifications" 
                                      class="form-control @error('specifications') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Technical specifications, grades, or additional details">{{ old('specifications') }}</textarea>
                            @error('specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.product-catalog.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Add Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection