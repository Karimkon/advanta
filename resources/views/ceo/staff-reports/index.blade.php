@extends('ceo.layouts.app')

@section('title', 'Staff Reports - CEO Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Staff Reports</h2>
            <p class="text-muted mb-0">View all submitted daily and weekly reports from staff</p>
        </div>
        <div class="btn-group">
            {{-- FIXED: Use the correct route name --}}
            <a href="{{ route('staff-reports.create') }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Submit Report
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Total Reports</h6>
                            <h2 class="fw-bold text-primary mb-1">{{ $stats['total'] }}</h2>
                            <small class="text-primary">All time reports</small>
                        </div>
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-file-text fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Daily Reports</h6>
                            <h2 class="fw-bold text-success mb-1">{{ $stats['daily'] }}</h2>
                            <small class="text-success">Daily progress reports</small>
                        </div>
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-sun fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Weekly Reports</h6>
                            <h2 class="fw-bold text-info mb-1">{{ $stats['weekly'] }}</h2>
                            <small class="text-info">Weekly summary reports</small>
                        </div>
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-calendar-week fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="card-subtitle text-muted mb-2">Today's Reports</h6>
                            <h2 class="fw-bold text-warning mb-1">{{ $stats['today'] }}</h2>
                            <small class="text-warning">Submitted today</small>
                        </div>
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-clock fs-4 text-warning"></i>
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
                        <option value="daily" {{ request('report_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ request('report_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Report Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Staff Name</label>
                    <input type="text" name="staff_name" class="form-control" placeholder="Search staff..." value="{{ request('staff_name') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('ceo.staff-reports.index') }}" class="btn btn-outline-secondary">Clear</a>
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
                    <i class="bi bi-file-text display-4 d-block mb-3"></i>
                    <h5>No Reports Found</h5>
                    <p class="mb-0">No staff reports match your criteria.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Staff</th>
                                <th>Type</th>
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
                                            {{ ucfirst($report->report_type) }}
                                        </span>
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
                                            <a href="{{ route('ceo.staff-reports.show', $report) }}" 
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
                                                        <h5 class="modal-title">Delete Report</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete this report?</p>
                                                        <p><strong>{{ $report->title }}</strong></p>
                                                        <p class="text-muted">This action cannot be undone.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form action="{{ route('ceo.staff-reports.destroy', $report) }}" method="POST">
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