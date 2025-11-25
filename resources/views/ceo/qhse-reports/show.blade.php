@extends('ceo.layouts.app')

@section('title', $qhseReport->title . ' - QHSE Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $qhseReport->title }}</h2>
            <p class="text-muted mb-0">QHSE Report Details</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('ceo.qhse-reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Report Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">QHSE Report Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Report Type:</strong> 
                                <span class="badge bg-{{ $qhseReport->getReportTypeBadgeClass() }}">
                                    {{ $qhseReport->getReportTypeLabel() }}
                                </span>
                            </p>
                            <p><strong>Report Date:</strong> {{ $qhseReport->report_date->format('F d, Y') }}</p>
                            <p><strong>Location:</strong> {{ $qhseReport->location }}</p>
                            <p><strong>Department:</strong> {{ $qhseReport->department }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Staff Name:</strong> {{ $qhseReport->staff_name }}</p>
                            <p><strong>Staff Email:</strong> {{ $qhseReport->staff_email }}</p>
                            <p><strong>Access Code:</strong> {{ $qhseReport->access_code }}</p>
                            <p><strong>Submitted:</strong> {{ $qhseReport->created_at->format('F d, Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-4">
                        <h6>Description</h6>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($qhseReport->description)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Attachments -->
            @if($qhseReport->attachments && count($qhseReport->attachments) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-paperclip"></i> Attachments
                        <span class="badge bg-primary">{{ count($qhseReport->attachments) }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($qhseReport->attachments as $index => $attachment)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <i class="bi bi-file-earmark me-2"></i>
                                <span class="small">{{ $attachment['name'] }}</span>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($attachment['size'] / 1024, 2) }} KB
                                </small>
                            </div>
                            <a href="{{ route('ceo.qhse-reports.download.attachment', ['qhseReport' => $qhseReport, 'index' => $index]) }}" 
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
                        <a href="{{ route('qhse-reports.create') }}" target="_blank" class="btn btn-outline-success">
                            <i class="bi bi-plus-circle"></i> Submit New QHSE Report
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
                <h5 class="modal-title">Delete QHSE Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this QHSE report?</p>
                <p><strong>{{ $qhseReport->title }}</strong></p>
                <p class="text-muted">This action cannot be undone and will remove all attachments.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('ceo.qhse-reports.destroy', $qhseReport) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection