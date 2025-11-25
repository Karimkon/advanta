@extends('ceo.layouts.app')

@section('title', 'QHSE Reports - CEO Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">QHSE Reports</h2>
            <p class="text-muted mb-0">Quality, Health, Safety & Environment Reports Overview</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('qhse-reports.create') }}" target="_blank" class="btn btn-outline-success">
                <i class="bi bi-plus-circle"></i> Submit QHSE Report
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Reports</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $stats['total'] }}</h2>
                            <small class="text-primary">All QHSE reports</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-shield-check fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Safety</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $stats['safety'] }}</h2>
                            <small class="text-warning">Safety reports</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Quality</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $stats['quality'] }}</h2>
                            <small class="text-info">Quality reports</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-award fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this after the Quality card -->
        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Company Documents</h6>
                            <h2 class="fw-bold text-secondary mb-1">{{ $stats['companydocuments'] }}</h2>
                            <small class="text-secondary">Company documents</small>
                        </div>
                        <div class="icon-wrapper bg-secondary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-folder fs-4 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Health</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $stats['health'] }}</h2>
                            <small class="text-success">Health reports</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-heart-pulse fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Environment</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $stats['environment'] }}</h2>
                            <small class="text-primary">Environment reports</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-tree fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Today</h6>
                            <h2 class="fw-bold text-danger mb-1">{{ $stats['today'] }}</h2>
                            <small class="text-danger">Submitted today</small>
                        </div>
                        <div class="icon-wrapper bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="safety" {{ request('report_type') == 'safety' ? 'selected' : '' }}>Safety</option>
                        <option value="quality" {{ request('report_type') == 'quality' ? 'selected' : '' }}>Quality</option>
                        <option value="companydocuments" {{ request('report_type') == 'companydocuments' ? 'selected' : '' }}>Company Documents</option>
                        <option value="health" {{ request('report_type') == 'health' ? 'selected' : '' }}>Health</option>
                        <option value="environment" {{ request('report_type') == 'environment' ? 'selected' : '' }}>Environment</option>
                        <option value="incident" {{ request('report_type') == 'incident' ? 'selected' : '' }}>Incident</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Report Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Location" value="{{ request('location') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" placeholder="Department" value="{{ request('department') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Staff Name</label>
                    <input type="text" name="staff_name" class="form-control" placeholder="Staff name" value="{{ request('staff_name') }}">
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('ceo.qhse-reports.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-shield-check display-4 d-block mb-3"></i>
                    <h5>No QHSE Reports Found</h5>
                    <p class="mb-0">No QHSE reports match your criteria.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Staff</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Attachments</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <strong>{{ Str::limit($report->title, 50) }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $report->staff_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $report->staff_email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $report->getReportTypeBadgeClass() }}">
                                            {{ $report->getReportTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($report->location, 20) }}</small>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($report->department, 20) }}</small>
                                    </td>
                                    <td>
                                        {{ $report->report_date->format('M d, Y') }}
                                    </td>
                                    <td>
                                        @if($report->getAttachmentsCount() > 0)
                                            <span class="badge bg-info">
                                                <i class="bi bi-paperclip"></i> {{ $report->getAttachmentsCount() }}
                                            </span>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $report->created_at->format('M d, Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('ceo.qhse-reports.show', $report) }}" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $report->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $report->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Delete QHSE Report</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete this QHSE report?</p>
                                                        <p><strong>{{ $report->title }}</strong></p>
                                                        <p class="text-muted">This action cannot be undone.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('ceo.qhse-reports.destroy', $report) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete Report</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection