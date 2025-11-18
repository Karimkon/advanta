@extends('surveyor.layouts.app')

@section('title', $milestone->title . ' - ' . $project->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $milestone->title }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('surveyor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('surveyor.milestones.index', $project) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active">{{ $milestone->title }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('surveyor.milestones.index', $project) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('surveyor.milestones.edit', ['project' => $project, 'milestone' => $milestone]) }}" 
               class="btn btn-warning">
                <i class="bi bi-pencil"></i> Update Progress
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Milestone Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Milestone Details</h5>
                </div>

                <!-- Photo Documentation -->
@if($milestone->hasPhoto())
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-camera"></i> Progress Photo
            @if($milestone->photo_caption)
                <small class="text-muted">- {{ $milestone->photo_caption }}</small>
            @endif
        </h5>
    </div>
    <div class="card-body text-center">
        <img src="{{ $milestone->getPhotoUrl() }}" 
             alt="Milestone progress photo" 
             class="img-fluid rounded" 
             style="max-height: 400px;">
        <div class="mt-3">
            <a href="{{ $milestone->getPhotoUrl() }}" 
               target="_blank" 
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-zoom-in"></i> View Full Size
            </a>
            <small class="text-muted d-block mt-2">
                Photo uploaded: {{ $milestone->updated_at->format('M d, Y H:i') }}
            </small>
        </div>
    </div>
</div>
@endif
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Project:</strong> {{ $project->name }}</p>
                            <p><strong>Description:</strong> {{ $milestone->description }}</p>
                            <p><strong>Due Date:</strong> {{ $milestone->due_date->format('M d, Y') }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $milestone->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estimated Cost:</strong> UGX {{ number_format($milestone->cost_estimate, 2) }}</p>
                            <p><strong>Actual Cost:</strong> 
                                @if($milestone->actual_cost)
                                    UGX {{ number_format($milestone->actual_cost, 2) }}
                                @else
                                    <span class="text-muted">Not recorded</span>
                                @endif
                            </p>
                            <p><strong>Completion:</strong> {{ $milestone->getProgressPercentage() }}%</p>
                            @if($milestone->completed_at)
                                <p><strong>Completed:</strong> {{ $milestone->completed_at->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Notes -->
                    @if($milestone->progress_notes)
                        <div class="mt-4">
                            <strong>Progress Notes:</strong>
                            <div class="border rounded p-3 mt-2 bg-light">
                                {{ $milestone->progress_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Visualization -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Progress Tracking</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div class="progress-circle-large" data-progress="{{ $milestone->getProgressPercentage() }}">
                                <span class="progress-circle-value">{{ $milestone->getProgressPercentage() }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="timeline">
                        <div class="timeline-item {{ $milestone->status === 'pending' ? 'active' : 'completed' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Pending</strong>
                                <small class="text-muted">Milestone created</small>
                            </div>
                        </div>
                        <div class="timeline-item {{ $milestone->status === 'in_progress' ? 'active' : ($milestone->status === 'completed' ? 'completed' : '') }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>In Progress</strong>
                                @if($milestone->status === 'in_progress' || $milestone->status === 'completed')
                                    <small class="text-muted">Work started</small>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ $milestone->status === 'completed' ? 'active' : '' }}">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <strong>Completed</strong>
                                @if($milestone->completed_at)
                                    <small class="text-muted">{{ $milestone->completed_at->format('M d, Y') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('surveyor.milestones.edit', ['project' => $project, 'milestone' => $milestone]) }}" 
                           class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Update Progress
                        </a>
                        <a href="{{ route('surveyor.milestones.index', $project) }}" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> All Milestones
                        </a>
                        <a href="{{ route('surveyor.dashboard') }}" 
                           class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Project Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Project:</strong> {{ $project->name }}</p>
                    <p><strong>Location:</strong> {{ $project->location }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'warning' }}">
                            {{ ucfirst($project->status) }}
                        </span>
                    </p>
                    <p><strong>Budget:</strong> UGX {{ number_format($project->budget, 2) }}</p>
                </div>
            </div>

            <!-- Deadline Alert -->
            @if($milestone->isOverdue())
                <div class="alert alert-danger mt-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Overdue!</strong> This milestone is past its due date.
                </div>
            @elseif($milestone->due_date->diffInDays(now()) <= 7)
                <div class="alert alert-warning mt-4">
                    <i class="bi bi-clock"></i>
                    <strong>Due Soon!</strong> This milestone is due in {{ $milestone->due_date->diffInDays(now()) }} days.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress-circle-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: conic-gradient(#198754 var(--progress), #e9ecef 0deg);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-circle-large::before {
    content: '';
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-circle-value {
    position: relative;
    font-weight: bold;
    font-size: 1.5rem;
    color: #198754;
    z-index: 1;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #dee2e6;
    border: 3px solid #fff;
}

.timeline-item.active .timeline-marker {
    background: #198754;
    border-color: #198754;
}

.timeline-item.completed .timeline-marker {
    background: #198754;
    border-color: #198754;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set progress circle value
    const circle = document.querySelector('.progress-circle-large');
    if (circle) {
        const progress = circle.getAttribute('data-progress');
        circle.style.setProperty('--progress', progress + '%');
    }
});
</script>
@endpush