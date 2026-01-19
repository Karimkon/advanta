@extends('admin.layouts.app')
@section('title', 'Add New Client')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add New Client</h2>
            <p class="text-muted mb-0">Create a new client account with project access</p>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Clients
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Client Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Client Information</h5>

                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This will be used for client login</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                                   value="{{ old('company') }}">
                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Project Assignment -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary"><i class="bi bi-folder"></i> Assign Projects</h5>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Select one or more projects that this client should have access to.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Projects</label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                @forelse($projects as $project)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="projects[]" value="{{ $project->id }}"
                                               class="form-check-input" id="project_{{ $project->id }}"
                                               {{ in_array($project->id, old('projects', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="project_{{ $project->id }}">
                                            <strong>{{ $project->name }}</strong>
                                            <br><small class="text-muted">{{ $project->location }} | {{ $project->code }}</small>
                                        </label>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No active projects available.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Create Client
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
