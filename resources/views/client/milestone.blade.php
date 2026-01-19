@extends('client.layouts.app')
@section('title', $milestone->title)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.projects.milestones', $project->id) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $milestone->title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Milestone Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h2 class="mb-2">{{ $milestone->title }}</h2>
                            <p class="text-muted mb-0">
                                <i class="bi bi-building me-1"></i> {{ $project->name }}
                                <span class="mx-2">|</span>
                                <i class="bi bi-geo-alt me-1"></i> {{ $project->location ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="text-end">
                            @php
                                $statusColors = [
                                    'pending' => 'secondary',
                                    'in_progress' => 'primary',
                                    'completed' => 'success',
                                    'delayed' => 'danger'
                                ];
                                $color = $statusColors[$milestone->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                                {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Description -->
            @if($milestone->description)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $milestone->description }}</p>
                </div>
            </div>
            @endif

            <!-- Progress Photo -->
            @if($milestone->photo_path)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i>Progress Photo</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <a href="{{ asset('storage/' . $milestone->photo_path) }}" data-lightbox="milestone-photo" data-title="{{ $milestone->title }}">
                            <img src="{{ asset('storage/' . $milestone->photo_path) }}"
                                 class="img-fluid rounded shadow"
                                 style="max-height: 500px; cursor: pointer;"
                                 alt="{{ $milestone->title }} progress photo">
                        </a>
                    </div>
                    @if($milestone->photo_caption)
                    <div class="mt-3 text-center">
                        <p class="text-muted mb-0"><i class="bi bi-chat-quote me-1"></i> {{ $milestone->photo_caption }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Progress Notes -->
            @if($milestone->progress_notes)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-sticky me-2"></i>Progress Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $milestone->progress_notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Progress Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Progress</h5>
                </div>
                <div class="card-body text-center">
                    @php
                        $progress = $milestone->completion_percentage ?? 0;
                    @endphp
                    <div class="progress-circle mx-auto mb-3" style="--progress: {{ $progress * 3.6 }}deg;">
                        <div class="progress-circle-inner">
                            <span class="fs-4 fw-bold">{{ $progress }}%</span>
                        </div>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar bg-{{ $color }}" role="progressbar"
                             style="width: {{ $progress }}%"
                             aria-valuenow="{{ $progress }}"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3">
                            <i class="bi bi-calendar-event text-primary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Target Date</small>
                            <strong>{{ $milestone->due_date ? date('M d, Y', strtotime($milestone->due_date)) : 'Not set' }}</strong>
                        </div>
                    </div>

                    @if($milestone->completed_at)
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Completed Date</small>
                            <strong>{{ date('M d, Y', strtotime($milestone->completed_at)) }}</strong>
                        </div>
                    </div>
                    @endif

                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-2 me-3">
                            <i class="bi bi-clock text-secondary"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Last Updated</small>
                            <strong>{{ date('M d, Y H:i', strtotime($milestone->updated_at)) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost Information (if available) -->
            @if($milestone->cost_estimate || $milestone->actual_cost)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Cost Information</h5>
                </div>
                <div class="card-body">
                    @if($milestone->cost_estimate)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Estimated Cost:</span>
                        <strong>UGX {{ number_format($milestone->cost_estimate, 0) }}</strong>
                    </div>
                    @endif
                    @if($milestone->actual_cost)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Actual Cost:</span>
                        <strong>UGX {{ number_format($milestone->actual_cost, 0) }}</strong>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Back Button -->
            <div class="d-grid">
                <a href="{{ route('client.projects.milestones', $project->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Project
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

<style>
.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#198754 var(--progress), #e9ecef var(--progress));
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-circle-inner {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<!-- Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
lightbox.option({
    'resizeDuration': 200,
    'wrapAround': true,
    'albumLabel': 'Progress Photo'
});
</script>
@endsection
