@extends('ceo.layouts.app')

@section('title', $staffReport->title . ' - Staff Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $staffReport->title }}</h2>
            <p class="text-muted mb-0">Staff Report Details</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.staff-reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Report Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Report Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Report Type:</strong> 
                                <span class="badge bg-{{ $staffReport->getReportTypeBadgeClass() }}">
                                    {{ ucfirst($staffReport->report_type) }}
                                </span>
                            </p>
                            <p><strong>Report Date:</strong> {{ $staffReport->report_date->format('F d, Y') }}</p>
                            <p><strong>Submitted:</strong> {{ $staffReport->created_at->format('F d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Staff Name:</strong> {{ $staffReport->staff_name }}</p>
                            <p><strong>Staff Email:</strong> {{ $staffReport->staff_email }}</p>
                            <p><strong>Access Code:</strong> {{ $staffReport->access_code }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-4">
                        <h6>Description</h6>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($staffReport->description)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Attachments -->
            @if($staffReport->attachments && count($staffReport->attachments) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-paperclip"></i> Attachments
                        <span class="badge bg-primary">{{ count($staffReport->attachments) }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($staffReport->attachments as $index => $attachment)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <i class="bi bi-file-earmark me-2"></i>
                                <span class="small">{{ $attachment['name'] }}</span>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($attachment['size'] / 1024, 2) }} KB
                                </small>
                            </div>
                            <a href="{{ route('ceo.staff-reports.download.attachment', ['staffReport' => $staffReport, 'index' => $index]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        {{-- FIXED: Use the correct route name --}}
                        <a href="{{ route('staff-reports.create') }}" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Submit New Report
                        </a>
                        <button type="button" class="btn btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal">
                            <i class="bi bi-trash"></i> Delete Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this report?</p>
                <p><strong>{{ $staffReport->title }}</strong></p>
                <p class="text-muted">This action cannot be undone and will remove all attachments.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('ceo.staff-reports.destroy', $staffReport) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection