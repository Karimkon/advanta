@extends($layout ?? 'finance.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Edit Office Staff</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'office-staff.index') }}">Office Staff</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-body">
                <form action="{{ route($routePrefix . 'office-staff.update', $officeStaff->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $officeStaff->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $officeStaff->email) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone" value="{{ old('phone', $officeStaff->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role / Job Title</label>
                            <input type="text" class="form-control" name="role" value="{{ old('role', $officeStaff->role) }}" placeholder="e.g. Operations Manager">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department">
                                <option value="">Select Department</option>
                                @foreach(['Operations', 'Finance', 'Administration', 'Marketing', 'IT', 'HR'] as $dept)
                                    <option value="{{ $dept }}" {{ old('department', $officeStaff->department) == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joined Date</label>
                            <input type="date" class="form-control" name="joined_date" value="{{ old('joined_date', optional($officeStaff->joined_date)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monthly Salary (UGX) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="salary" value="{{ old('salary', $officeStaff->salary) }}" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ $officeStaff->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $officeStaff->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('finance.office-staff.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
