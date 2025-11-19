@extends('admin.layouts.app')

@section('title', 'Create Milestone - ' . $project->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create New Milestone</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.milestones.index') }}">All Milestones</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.milestones.project', $project) }}">{{ $project->name }}</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.milestones.project', $project) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Cancel
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Milestone Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.milestones.store', $project) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-4">
                            <!-- Title -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Milestone Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" 
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ old('title') }}"
                                           placeholder="e.g., Foundation (Omusingi), Roofing Level, etc."
                                           required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" 
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="3" 
                                              placeholder="Describe the work involved in this milestone...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" name="due_date" 
                                           class="form-control @error('due_date') is-invalid @enderror"
                                           value="{{ old('due_date') }}"
                                           min="{{ now()->format('Y-m-d') }}"
                                           required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="delayed" {{ old('status') === 'delayed' ? 'selected' : '' }}>Delayed</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Cost Estimate -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Cost Estimate (UGX)</label>
                                    <input type="number" name="cost_estimate" 
                                           class="form-control @error('cost_estimate') is-invalid @enderror"
                                           step="0.01" min="0"
                                           value="{{ old('cost_estimate') }}"
                                           placeholder="Estimated cost for this milestone">
                                    @error('cost_estimate')
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
                                           value="{{ old('completion_percentage', 0) }}"
                                           oninput="updatePercentageValue(this.value)">
                                    <div class="d-flex justify-content-between">
                                        <small>0%</small>
                                        <small id="percentageValue">{{ old('completion_percentage', 0) }}%</small>
                                        <small>100%</small>
                                    </div>
                                    @error('completion_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                </div>
                            </div>

                            <!-- Photo Caption -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Photo Caption</label>
                                    <input type="text" name="photo_caption" 
                                           class="form-control @error('photo_caption') is-invalid @enderror"
                                           value="{{ old('photo_caption') }}"
                                           placeholder="Describe what the photo shows...">
                                    @error('photo_caption')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('admin.milestones.project', $project) }}" 
                                       class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Create Milestone
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
</script>
@endpush