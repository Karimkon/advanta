@extends('operations.layouts.app')

@section('title', 'QHSE Report - ' . $qhseReport->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">QHSE Report: {{ $qhseReport->title }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('operations.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('operations.qhse-reports.index') }}">QHSE Reports</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($qhseReport->title, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('operations.qhse-reports.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Report Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Report Type:</strong>
                            <span class="badge bg-{{ $qhseReport->getReportTypeBadgeClass() }} ms-2">
                                {{ $qhseReport->getReportTypeLabel() }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Report Date:</strong>
                            <span class="ms-2">{{ $qhseReport->report_date->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Location:</strong>
                            <span class="ms-2">{{ $qhseReport->location }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Department:</strong>
                            <span class="ms-2">{{ $qhseReport->department }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {{ $qhseReport->description }}
                        </div>
                    </div>

                    @if($qhseReport->getAttachmentsCount() > 0)
                    <div class="mb-3">
                        <strong>Attachments:</strong>
                        <div class="mt-2">
                            @foreach($qhseReport->getAttachmentsArray() as $index => $attachment)
                                @php
                                    // Handle both formats: object with 'name' key or plain string
                                    $attachmentName = is_array($attachment) ? ($attachment['name'] ?? basename($attachment['path'] ?? 'Attachment')) : basename($attachment);
                                @endphp
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-paperclip me-2"></i>
                                    <span class="me-3">{{ $attachmentName }}</span>
                                    <a href="{{ route('operations.qhse-reports.download', ['qhseReport' => $qhseReport->id, 'index' => $index]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Staff Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Staff Name:</strong>
                        <div>{{ $qhseReport->staff_name }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <div>{{ $qhseReport->staff_email }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Submitted:</strong>
                        <div>{{ $qhseReport->created_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection