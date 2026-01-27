@extends('admin.layouts.app')
@section('title', 'Edit Subcontractor')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Subcontractor</h2>
            <p class="text-muted mb-0">Update: {{ $subcontractor->name }}</p>
        </div>
        <a href="{{ route('admin.subcontractors.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Subcontractors
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.subcontractors.update', $subcontractor) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Basic Information</h5>

                        <div class="mb-3">
                            <label class="form-label">Company/Business Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $subcontractor->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                                   value="{{ old('contact_person', $subcontractor->contact_person) }}">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $subcontractor->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $subcontractor->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to keep current password</small>
                        </div>
                    </div>

                    <!-- Business Details -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary"><i class="bi bi-building"></i> Business Details</h5>

                        <div class="mb-3">
                            <label class="form-label">Specialization <span class="text-danger">*</span></label>
                            <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror"
                                   value="{{ old('specialization', $subcontractor->specialization) }}" required>
                            @error('specialization')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tax Number (TIN/PIN)</label>
                            <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror"
                                   value="{{ old('tax_number', $subcontractor->tax_number) }}">
                            @error('tax_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $subcontractor->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $subcontractor->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $subcontractor->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($subcontractor->last_login_at)
                            <div class="alert alert-info">
                                <i class="bi bi-clock"></i> Last login: {{ $subcontractor->last_login_at->format('M d, Y H:i') }}
                            </div>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.subcontractors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update Subcontractor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Existing Contracts -->
    @if($subcontractor->projectSubcontractors->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-file-text"></i> Project Contracts</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Contract #</th>
                            <th>Project</th>
                            <th>Work Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subcontractor->projectSubcontractors as $contract)
                            <tr>
                                <td><strong>{{ $contract->contract_number }}</strong></td>
                                <td>{{ $contract->project->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($contract->work_description, 50) }}</td>
                                <td>KES {{ number_format($contract->contract_amount, 0) }}</td>
                                <td>
                                    <span class="badge bg-{{ $contract->status === 'active' ? 'success' : ($contract->status === 'completed' ? 'info' : 'danger') }}">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.subcontractors.contracts.edit', ['projectSubcontractor' => $contract->id]) }}" class="btn btn-outline-warning" title="Edit Contract">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.subcontractors.contracts.destroy', ['projectSubcontractor' => $contract->id]) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this contract?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Contract">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
