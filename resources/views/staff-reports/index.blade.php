@extends('admin.layouts.app') {{-- Or ceo.layouts.app --}}

@section('title', 'Staff Reports - Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Staff Reports</h2>
            <p class="text-muted mb-0">Daily and weekly reports from staff</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
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
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                    <a href="{{ route('staff-reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-clipboard-data text-primary me-2"></i>
                All Staff Reports
            </h5>
            <span class="badge bg-primary">{{ $reports->total() }} reports</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <strong>{{ Str::limit($report->title, 50) }}</strong>
                                </td>
                                <td>
                                    <div>{{ $report->staff_name }}</div>
                                    <small class="text-muted">{{ $report->staff_email }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $report->getReportTypeBadgeClass() }}">
                                        {{ ucfirst($report->report_type) }}
                                    </span>
                                </td>
                                <td>{{ $report->report_date->format('M d, Y') }}</td>
                                <td>
                                    @if($report->getAttachmentsCount() > 0)
                                        <span class="badge bg-info">
                                            <i class="bi bi-paperclip"></i> {{ $report->getAttachmentsCount() }}
                                        </span>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>{{ $report->created_at->diffForHumans() }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('staff-reports.show', $report) }}" 
                                           class="btn btn-outline-primary" title="View Report">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form action="{{ route('staff-reports.destroy', $report) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" 
                                                    onclick="return confirm('Delete this report?')"
                                                    title="Delete Report">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-clipboard-x display-4 d-block mb-2"></i>
                                        No staff reports found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($reports->hasPages())
                <div class="card-footer bg-white">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection