@extends('client.layouts.app')
@section('title', $project->name)

@section('content')
<div class="container-fluid">
    <!-- Project Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <a href="{{ route('client.dashboard') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="bi bi-arrow-left"></i> Back to Projects
                    </a>
                    <h2 class="mb-1">{{ $project->name }}</h2>
                    <p class="text-muted mb-0">{{ $project->code }} • {{ $project->location ?? 'N/A' }}</p>
                </div>
                <div>
                    @php
                        $statusColors = [
                            'planning' => 'secondary',
                            'in_progress' => 'primary',
                            'on_hold' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$project->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-check text-primary fs-3"></i>
                    <h6 class="text-muted mt-2 mb-1">Start Date</h6>
                    <p class="fw-semibold mb-0">
                        {{ $project->start_date ? date('M d, Y', strtotime($project->start_date)) : 'Not set' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-x text-danger fs-3"></i>
                    <h6 class="text-muted mt-2 mb-1">End Date</h6>
                    <p class="fw-semibold mb-0">
                        {{ $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'Not set' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up text-success fs-3"></i>
                    <h6 class="text-muted mt-2 mb-1">Overall Progress</h6>
                    <div class="progress mx-auto" style="height: 12px; width: 80%;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ $project->progress ?? 0 }}%"
                             aria-valuenow="{{ $project->progress ?? 0 }}"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <p class="fw-semibold mb-0 mt-2">{{ number_format($project->progress ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-flag-fill text-warning fs-3"></i>
                    <h6 class="text-muted mt-2 mb-1">Milestones</h6>
                    <p class="fw-semibold mb-0">
                        {{ $project->milestones()->where('status', 'completed')->count() }} / {{ $project->milestones()->count() }} Completed
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Description -->
    @if($project->description)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Project Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $project->description }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Milestones Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bi bi-flag me-2"></i>Project Milestones</h5>
                </div>
                <div class="card-body">
                    @if($project->milestones->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No milestones added yet</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($project->milestones()->orderBy('due_date', 'asc')->get() as $index => $milestone)
                            <div class="timeline-item mb-4">
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        <div class="timeline-badge
                                            @if($milestone->status === 'completed') bg-success
                                            @elseif($milestone->status === 'in_progress') bg-primary
                                            @elseif($milestone->status === 'delayed') bg-danger
                                            @else bg-secondary
                                            @endif
                                        ">
                                            <i class="bi
                                                @if($milestone->status === 'completed') bi-check-circle-fill
                                                @elseif($milestone->status === 'in_progress') bi-clock-fill
                                                @elseif($milestone->status === 'delayed') bi-exclamation-triangle-fill
                                                @else bi-circle-fill
                                                @endif
                                            "></i>
                                        </div>
                                        @if(!$loop->last)
                                        <div class="timeline-line"></div>
                                        @endif
                                    </div>
                                    <div class="col-md-10">
                                        <div class="card border mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1">{{ $milestone->title }}</h6>
                                                        <p class="text-muted mb-0 small">
                                                            <i class="bi bi-calendar3"></i>
                                                            Target: {{ date('M d, Y', strtotime($milestone->due_date)) }}
                                                            @if($milestone->completed_at)
                                                                • Completed: {{ date('M d, Y', strtotime($milestone->completed_at)) }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <span class="badge
                                                        @if($milestone->status === 'completed') bg-success
                                                        @elseif($milestone->status === 'in_progress') bg-primary
                                                        @elseif($milestone->status === 'delayed') bg-danger
                                                        @else bg-secondary
                                                        @endif
                                                    ">
                                                        {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                                    </span>
                                                </div>

                                                @if($milestone->description)
                                                <p class="mb-3">{{ $milestone->description }}</p>
                                                @endif

                                                <!-- Progress Bar -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">Progress</small>
                                                        <small class="fw-semibold">{{ $milestone->completion_percentage ?? 0 }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar
                                                            @if($milestone->status === 'completed') bg-success
                                                            @elseif($milestone->status === 'in_progress') bg-primary
                                                            @elseif($milestone->status === 'delayed') bg-danger
                                                            @else bg-secondary
                                                            @endif
                                                        " role="progressbar"
                                                             style="width: {{ $milestone->completion_percentage ?? 0 }}%"
                                                             aria-valuenow="{{ $milestone->completion_percentage ?? 0 }}"
                                                             aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Milestone Photo -->
                                                @if($milestone->photo_path)
                                                    <div class="milestone-images">
                                                        <h6 class="text-muted small mb-2">
                                                            <i class="bi bi-image"></i> Progress Photo
                                                        </h6>
                                                        <div class="row g-2">
                                                            <div class="col-md-4 col-sm-6">
                                                                <a href="{{ asset('storage/' . $milestone->photo_path) }}" data-lightbox="milestone-{{ $milestone->id }}" data-title="{{ $milestone->title }}">
                                                                    <img src="{{ asset('storage/' . $milestone->photo_path) }}"
                                                                         class="img-fluid rounded shadow-sm milestone-thumb"
                                                                         alt="Milestone progress photo">
                                                                </a>
                                                                @if($milestone->photo_caption)
                                                                    <small class="text-muted d-block mt-1">{{ $milestone->photo_caption }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Notes -->
                                                @if($milestone->progress_notes)
                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <small class="text-muted d-block mb-1"><i class="bi bi-sticky"></i> Notes:</small>
                                                    <small>{{ $milestone->progress_notes }}</small>
                                                </div>
                                                @endif

                                                <!-- View Details Button -->
                                                <div class="mt-3 text-end">
                                                    <a href="{{ route('client.projects.milestone.detail', ['project' => $project->id, 'milestone' => $milestone->id]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

<style>
.timeline-item {
    position: relative;
}

.timeline-badge {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-line {
    width: 2px;
    height: calc(100% + 20px);
    background: #dee2e6;
    margin: 10px auto;
}

.milestone-thumb {
    cursor: pointer;
    transition: transform 0.2s;
    height: 120px;
    object-fit: cover;
    width: 100%;
}

.milestone-thumb:hover {
    transform: scale(1.05);
}
</style>

<!-- Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
lightbox.option({
    'resizeDuration': 200,
    'wrapAround': true,
    'albumLabel': 'Image %1 of %2'
});
</script>
@endsection
