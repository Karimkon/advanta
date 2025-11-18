@extends('surveyor.layouts.app')

@section('title', 'Update ' . $milestone->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Update Milestone Progress</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('surveyor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('surveyor.milestones.index', $project) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('surveyor.milestones.show', ['project' => $project, 'milestone' => $milestone]) }}">{{ $milestone->title }}</a></li>
                    <li class="breadcrumb-item active">Update</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('surveyor.milestones.show', ['project' => $project, 'milestone' => $milestone]) }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">{{ $milestone->title }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('surveyor.milestones.update', ['project' => $project, 'milestone' => $milestone]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Current Milestone Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Project:</strong> {{ $project->name }}</p>
                                <p><strong>Description:</strong> {{ $milestone->description }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Due Date:</strong> {{ $milestone->due_date->format('M d, Y') }}</p>
                                <p><strong>Estimated Cost:</strong> UGX {{ number_format($milestone->cost_estimate, 2) }}</p>
                            </div>
                        </div>

                        <hr>

                        <!-- Progress Update Form -->
                        <div class="row g-4">
                            <!-- Status -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ old('status', $milestone->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ old('status', $milestone->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status', $milestone->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="delayed" {{ old('status', $milestone->status) === 'delayed' ? 'selected' : '' }}>Delayed</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Completion Percentage -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Completion Percentage <span class="text-danger">*</span></label>
                                    <input type="range" name="completion_percentage" 
                                           class="form-range @error('completion_percentage') is-invalid @enderror"
                                           min="0" max="100" step="5"
                                           value="{{ old('completion_percentage', $milestone->completion_percentage ?? 0) }}"
                                           oninput="updatePercentageValue(this.value)">
                                    <div class="d-flex justify-content-between">
                                        <small>0%</small>
                                        <small id="percentageValue">{{ old('completion_percentage', $milestone->completion_percentage ?? 0) }}%</small>
                                        <small>100%</small>
                                    </div>
                                    @error('completion_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Actual Cost -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Actual Cost (UGX)</label>
                                    <input type="number" name="actual_cost" 
                                           class="form-control @error('actual_cost') is-invalid @enderror"
                                           step="0.01" min="0"
                                           value="{{ old('actual_cost', $milestone->actual_cost) }}"
                                           placeholder="Enter actual cost if known">
                                    @error('actual_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Estimated: UGX {{ number_format($milestone->cost_estimate, 2) }}
                                    </small>
                                </div>
                            </div>

                            <!-- Photo Upload -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Progress Photo (Optional)</label>
                                    <input type="file" name="photo" 
                                           class="form-control @error('photo') is-invalid @enderror"
                                           accept="image/*"
                                           onchange="previewImage(this)">
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Upload a photo showing current progress (Max: 5MB, JPG, PNG, GIF)
                                    </small>
                                    
                                    <!-- Image Preview -->
                                    <div id="imagePreview" class="mt-2" style="display: none;">
                                        <img id="preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>

                                    <!-- Current Photo -->
                                    @if($milestone->hasPhoto())
                                        <div class="mt-3">
                                            <label class="form-label">Current Photo</label>
                                            <div class="border rounded p-3">
                                                <img src="{{ $milestone->getPhotoUrl() }}" 
                                                     alt="Current milestone photo" 
                                                     class="img-fluid rounded mb-2" 
                                                     style="max-height: 200px;">
                                                @if($milestone->photo_caption)
                                                    <p class="mb-2"><small>{{ $milestone->photo_caption }}</small></p>
                                                @endif
                                                <div class="d-flex gap-2">
                                                    <a href="{{ $milestone->getPhotoUrl() }}" 
                                                       target="_blank" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-zoom-in"></i> View Full Size
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmPhotoRemoval()">
                                                        <i class="bi bi-trash"></i> Remove Photo
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Photo Caption -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Photo Caption</label>
                                    <input type="text" name="photo_caption" 
                                           class="form-control @error('photo_caption') is-invalid @enderror"
                                           value="{{ old('photo_caption', $milestone->photo_caption) }}"
                                           placeholder="Describe what the photo shows...">
                                    @error('photo_caption')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Optional description for the progress photo
                                    </small>
                                </div>
                            </div>

                            <!-- Progress Notes -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Progress Notes</label>
                                    <textarea name="progress_notes" 
                                              class="form-control @error('progress_notes') is-invalid @enderror"
                                              rows="5" 
                                              placeholder="Describe the current progress, any challenges faced, observations, or additional details...">{{ old('progress_notes', $milestone->progress_notes) }}</textarea>
                                    @error('progress_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Provide detailed notes about the milestone progress, quality of work, and any issues encountered.
                                    </small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('surveyor.milestones.show', ['project' => $project, 'milestone' => $milestone]) }}" 
                                       class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Update Progress
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Photo Removal Form -->
                    @if($milestone->hasPhoto())
                        <form id="removePhotoForm" 
                              action="{{ route('surveyor.milestones.remove-photo', ['project' => $project, 'milestone' => $milestone]) }}" 
                              method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card border-info mt-4">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Surveyor Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Update status to reflect current work phase</li>
                        <li>Set completion percentage based on actual work done</li>
                        <li>Record actual costs when payments are made</li>
                        <li>Provide detailed notes for project transparency</li>
                        <li><strong>Upload progress photos</strong> to document each stage visually</li>
                        <li>Mark as completed only when all work is finished and inspected</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updatePercentageValue(value) {
    document.getElementById('percentageValue').textContent = value + '%';
}

function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.style.display = 'none';
    }
}

function confirmPhotoRemoval() {
    if (confirm('Are you sure you want to remove this photo? This action cannot be undone.')) {
        document.getElementById('removePhotoForm').submit();
    }
}

// Initialize preview if there's already a file selected (after validation error)
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.querySelector('input[name="photo"]');
    if (photoInput && photoInput.files.length > 0) {
        previewImage(photoInput);
    }
});
</script>

<style>
.img-thumbnail {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.25rem;
    background-color: #fff;
}
</style>
@endpush