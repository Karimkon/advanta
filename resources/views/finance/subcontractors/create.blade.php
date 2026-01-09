{{-- resources/views/finance/subcontractors/create.blade.php --}}
@extends('finance.layouts.app')

@section('title', 'Add Subcontractor')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add New Subcontractor</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.subcontractors.index') }}">Subcontractors</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.subcontractors.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('finance.subcontractors.store') }}" method="POST" id="subcontractorForm">
                @csrf

                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Company/Individual Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="specialization" class="form-label">Specialization *</label>
                        <input type="text" class="form-control" id="specialization" name="specialization" 
                               value="{{ old('specialization') }}" placeholder="e.g., Pavers, Electrical, Plumbing" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        <small class="text-muted">Used for portal login</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Portal Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Leave blank to disable login">
                        <small class="text-muted">If set, subcontractor can login to make requisitions</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tax_number" class="form-label">Tax Number</label>
                        <input type="text" class="form-control" id="tax_number" name="tax_number" value="{{ old('tax_number') }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                    </div>
                </div>

                <!-- Project Contracts -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3">Project Contracts</h5>
                        <div id="projects-container">
                            <div class="project-contract card mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Project *</label>
                                            <select class="form-select project-select" name="projects[0][project_id]" required>
                                                <option value="">Select Project</option>
                                                @foreach($projects as $project)
                                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Contract Amount (UGX) *</label>
                                            <input type="number" step="0.01" class="form-control contract-amount" 
                                                   name="projects[0][contract_amount]" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Start Date *</label>
                                            <input type="date" class="form-control" name="projects[0][start_date]" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Work Description *</label>
                                            <textarea class="form-control" name="projects[0][work_description]" rows="2" 
                                                      placeholder="Describe the work to be done..." required></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Terms & Conditions</label>
                                            <textarea class="form-control" name="projects[0][terms]" rows="2" 
                                                      placeholder="Payment terms, deliverables, etc."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-project">
                            <i class="bi bi-plus"></i> Add Another Project
                        </button>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save Subcontractor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let projectCount = 1;
    
    document.getElementById('add-project').addEventListener('click', function() {
        const container = document.getElementById('projects-container');
        const newProject = document.querySelector('.project-contract').cloneNode(true);
        
        // Update all input names with new index
        const inputs = newProject.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[0]', `[${projectCount}]`));
                input.value = ''; // Clear values
            }
        });
        
        container.appendChild(newProject);
        projectCount++;
    });
});
</script>
@endsection