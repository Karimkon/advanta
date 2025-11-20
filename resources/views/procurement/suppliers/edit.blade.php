@extends('procurement.layouts.app')

@section('title', 'Edit Supplier - Procurement')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Supplier: {{ $supplier->name }}</h5>
                        <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Suppliers
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('procurement.suppliers.update', $supplier) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <!-- Supplier Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $supplier->name) }}" placeholder="Enter supplier name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Supplier Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code', $supplier->code) }}" placeholder="e.g., SUP-001" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_person" id="contact_person" class="form-control @error('contact_person') is-invalid @enderror" 
                                           value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="Contact person name" required>
                                    @error('contact_person')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $supplier->phone) }}" placeholder="+256 XXX XXX XXX" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $supplier->email) }}" placeholder="supplier@example.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        <option value="Construction" {{ old('category', $supplier->category) == 'Construction' ? 'selected' : '' }}>Construction</option>
                                        <option value="Hardware" {{ old('category', $supplier->category) == 'Hardware' ? 'selected' : '' }}>Hardware</option>
                                        <option value="Electrical" {{ old('category', $supplier->category) == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                        <option value="Plumbing" {{ old('category', $supplier->category) == 'Plumbing' ? 'selected' : '' }}>Plumbing</option>
                                        <option value="Office Supplies" {{ old('category', $supplier->category) == 'Office Supplies' ? 'selected' : '' }}>Office Supplies</option>
                                        <option value="IT Equipment" {{ old('category', $supplier->category) == 'IT Equipment' ? 'selected' : '' }}>IT Equipment</option>
                                        <option value="Other" {{ old('category', $supplier->category) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Rating & Status -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating (0-5)</label>
                                    <input type="number" name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror" 
                                           value="{{ old('rating', $supplier->rating) }}" min="0" max="5" step="0.1" placeholder="4.5">
                                    <small class="text-muted">Rate the supplier from 0 to 5 stars</small>
                                    @error('rating')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status', $supplier->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $supplier->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" 
                                              rows="3" placeholder="Full supplier address" required>{{ old('address', $supplier->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3" placeholder="Any additional notes about the supplier">{{ old('notes', $supplier->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Supplier Statistics (Readonly) -->
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Supplier Statistics</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>Total LPOs:</strong> {{ $supplier->lpos->count() }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>Active LPOs:</strong> {{ $supplier->lpos->where('status', 'issued')->count() }}</p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="mb-1"><strong>Total Business:</strong> UGX {{ number_format($supplier->lpos->sum('total'), 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <div class="btn-group">
                                        <a href="{{ route('procurement.suppliers.show', $supplier) }}" class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Update Supplier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection