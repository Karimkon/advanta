{{-- resources/views/finance/labor/import.blade.php --}}
@extends('finance.layouts.app')

@section('title', 'Import Labor Workers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Import Labor Workers</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.labor.index') }}">Labor Workers</a></li>
                    <li class="breadcrumb-item active">Import</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.labor.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Bulk Import Workers</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.labor.process-import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="project_id" class="form-label">Project *</label>
                                <select class="form-select" id="project_id" name="project_id" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-4">
                                <label for="import_file" class="form-label">Excel File *</label>
                                <input type="file" class="form-control" id="import_file" name="import_file" 
                                       accept=".xlsx,.xls,.csv" required>
                                <small class="text-muted">
                                    Upload Excel file with worker data. File should be in the template format.
                                </small>
                            </div>
                        </div>

                        @if(session('import_errors'))
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-exclamation-triangle"></i> Import Warnings</h6>
                                <ul class="mb-0">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="text-end">
                            <a href="{{ route('finance.labor.download-template') }}" class="btn btn-outline-primary me-2">
                                <i class="bi bi-download"></i> Download Template
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload"></i> Import Workers
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Instructions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Import Instructions</h5>
                </div>
                <div class="card-body">
                    <h6>Required Fields:</h6>
                    <ul class="small">
                        <li><strong>Name*</strong> - Full name of worker</li>
                        <li><strong>Role*</strong> - Job role/position</li>
                        <li><strong>Payment Frequency*</strong> - daily, weekly, or monthly</li>
                    </ul>

                    <h6>Optional Fields:</h6>
                    <ul class="small">
                        <li><strong>Phone</strong> - Phone number</li>
                        <li><strong>Email</strong> - Email address</li>
                        <li><strong>ID Number</strong> - National ID</li>
                        <li><strong>NSSF Number</strong> - NSSF membership number</li>
                        <li><strong>Bank Name</strong> - Bank name</li>
                        <li><strong>Bank Account</strong> - Bank account number</li>
                        <li><strong>Daily Rate</strong> - Required for daily/weekly workers</li>
                        <li><strong>Monthly Rate</strong> - Required for monthly workers</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="bi bi-info-circle"></i> 
                            <strong>Note:</strong> For daily/weekly workers, fill Daily Rate. For monthly workers, fill Monthly Rate.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Sample Data -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Sample Data</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Frequency</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>John Doe</td>
                                    <td>Mason</td>
                                    <td>daily</td>
                                    <td>50000</td>
                                </tr>
                                <tr>
                                    <td>Jane Smith</td>
                                    <td>Carpenter</td>
                                    <td>monthly</td>
                                    <td>1200000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection