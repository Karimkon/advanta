@extends('admin.layouts.app')
@section('title', 'Edit User - ' . $user->name)
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit User</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">Edit {{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf 
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                    <small class="text-muted">Email cannot be changed</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}" placeholder="+256 XXX XXX XXX">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">User Role <span class="text-danger">*</span></label>
                                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}" 
                                                {{ old('role', $user->role) === $role ? 'selected' : '' }}>
                                                @if($role === 'engineer')
                                                    Engineer
                                                @else
                                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Password Reset Section -->
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="bi bi-key me-2"></i>Password Reset (Optional)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="password" 
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       placeholder="Leave blank to keep current password">
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Confirm New Password</label>
                                                <input type="password" name="password_confirmation" 
                                                       class="form-control" 
                                                       placeholder="Confirm new password">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Password must be at least 6 characters long. Leave both fields empty to keep the current password.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- User Statistics (Read-only) -->
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="bi bi-graph-up me-2"></i>User Activity Summary
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="border rounded p-3">
                                                    <h5 class="text-primary mb-1">{{ $user->requisitions->count() }}</h5>
                                                    <small class="text-muted">Requisitions</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border rounded p-3">
                                                    <h5 class="text-success mb-1">{{ $user->approvals->count() }}</h5>
                                                    <small class="text-muted">Approvals</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border rounded p-3">
                                                    <h5 class="text-warning mb-1">{{ $user->projects->count() }}</h5>
                                                    <small class="text-muted">Projects</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border rounded p-3">
                                                    <h5 class="text-info mb-1">{{ $user->receivedDeliveries->count() }}</h5>
                                                    <small class="text-muted">Deliveries</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center border-top pt-4">
                                    <div>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info">
                                                <i class="bi bi-person-check me-1"></i>This is your account
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i> Update User
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            @if($user->id !== auth()->id())
            <div class="card border-danger mt-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Delete User Account</h6>
                            <p class="text-muted mb-0">
                                Once deleted, this user account cannot be recovered. All associated data will be preserved but marked as inactive.
                            </p>
                        </div>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                <i class="bi bi-trash me-1"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: 1px solid #e9ecef;
}
.card-header {
    border-bottom: 1px solid #e9ecef;
}
</style>
@endpush