@extends($layout ?? 'finance.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Office Staff Management</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix . 'dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Office Staff</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-sm-4">
                        <div class="search-box me-2 mb-2 d-inline-block">
                            <div class="position-relative">
                                <input type="text" class="form-control" placeholder="Search...">
                                <i class="bx bx-search-alt search-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="text-sm-end">
                            <a href="{{ route($routePrefix . 'office-staff.create') }}" class="btn btn-success btn-rounded waves-effect waves-light mb-2 me-2">
                                <i class="mdi mdi-plus me-1"></i> Add New Staff
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 70px;">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Role</th>
                                <th scope="col">Department</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Salary (UGX)</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $member)
                            <tr>
                                <td>
                                    <div class="avatar-xs">
                                        <span class="avatar-title rounded-circle">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <h5 class="font-size-14 mb-1"><a href="{{ route($routePrefix . 'office-staff.show', $member->id) }}" class="text-dark">{{ $member->name }}</a></h5>
                                    <p class="text-muted mb-0">{{ $member->email }}</p>
                                </td>
                                <td>{{ $member->role }}</td>
                                <td>{{ $member->department }}</td>
                                <td>{{ $member->phone }}</td>
                                <td>{{ number_format($member->salary) }}</td>
                                <td>
                                    <span class="badge bg-{{ $member->status == 'active' ? 'success' : 'danger' }} font-size-12">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route($routePrefix . 'office-staff.show', $member->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                        <a href="{{ route($routePrefix . 'office-staff.create-payment', $member->id) }}" class="btn btn-sm btn-success" title="Record Payment">
                                            <i class="bx bx-money"></i> Pay
                                        </a>
                                        <a href="{{ route($routePrefix . 'office-staff.edit', $member->id) }}" class="btn btn-sm btn-info" title="Edit">
                                            <i class="bx bx-pencil"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No office staff found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
