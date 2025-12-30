@extends('admin.layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Create New User</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the errors below:</strong>
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="card p-4 shadow-sm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" required value="{{ old('phone') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
            <small class="text-muted">Minimum 6 characters</small>
        </div>

        <div class="mb-3">
            <label class="form-label">User Role</label>
            <select name="role" id="roleSelect" class="form-select" required>
                <option value="">Select Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_',' ', $role)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Store Assignment (shown only for stores role) -->
        <div class="mb-3" id="storeAssignmentSection" style="{{ old('role') === 'stores' ? '' : 'display: none;' }}">
            <label class="form-label">Assign to Store</label>
            <select name="shop_id" id="shopSelect" class="form-select">
                <option value="">No Store Assigned</option>
                @foreach($availableStores as $store)
                    <option value="{{ $store->id }}" {{ old('shop_id') == $store->id ? 'selected' : '' }}>
                        {{ $store->name }} ({{ ucfirst($store->type) }})
                    </option>
                @endforeach
            </select>
            <small class="text-muted">
                Only stores without a manager are shown.
                @if($availableStores->isEmpty())
                    <span class="text-warning">All stores already have managers assigned.</span>
                @endif
            </small>
        </div>

        <button class="btn btn-primary">Create User</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    const storeSection = document.getElementById('storeAssignmentSection');
    if (this.value === 'stores') {
        storeSection.style.display = 'block';
    } else {
        storeSection.style.display = 'none';
        document.getElementById('shopSelect').value = '';
    }
});
</script>
@endpush
@endsection
