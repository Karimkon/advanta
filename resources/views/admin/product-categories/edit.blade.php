@extends('admin.layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><strong>Edit Category</strong></h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.product-categories.update', $productCategory) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $productCategory->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select name="parent_id" id="parent_id" 
                                    class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">-- No Parent (Root Category) --</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}" 
                                        {{ old('parent_id', $productCategory->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $productCategory->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" 
                                           class="form-control @error('sort_order') is-invalid @enderror" 
                                           value="{{ old('sort_order', $productCategory->sort_order) }}" 
                                           min="0">
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                               value="1" {{ old('is_active', $productCategory->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Category
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Category Usage Info -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><strong>Category Information</strong></h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="p-3">
                                <h4 class="text-primary">{{ $productCategory->products_count }}</h4>
                                <p class="text-muted mb-0">Products</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3">
                                <h4 class="text-success">{{ $productCategory->children_count ?? 0 }}</h4>
                                <p class="text-muted mb-0">Subcategories</p>
                            </div>
                        </div>
                    </div>
                    
                    @if(!$productCategory->canBeDeleted())
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            This category cannot be deleted because it has associated products or subcategories.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection