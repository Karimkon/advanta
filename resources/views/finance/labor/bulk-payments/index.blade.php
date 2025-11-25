@extends('finance.layouts.app')

@section('title', 'Bulk Labor Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Bulk Labor Payments</h2>
            <p class="text-muted mb-0">Process multiple labor payments at once</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('finance.labor.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Labor
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Manual Bulk Entry</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.labor.bulk-payments.create') }}" method="GET">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project *</label>
                                <select name="project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Month *</label>
                                <input type="month" name="payment_month" class="form-control" 
                                       value="{{ date('Y-m') }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Enter Payments Manually
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Excel Import</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('finance.labor.bulk-payments.download-template') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project *</label>
                                <select name="project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Month *</label>
                                <input type="month" name="payment_month" class="form-control" 
                                       value="{{ date('Y-m') }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-download"></i> Download Excel Template
                        </button>
                    </form>

                    <hr>
                    
                    <form action="{{ route('finance.labor.bulk-payments.import') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Upload Filled Template</label>
                            <input type="file" name="import_file" class="form-control" 
                                   accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" name="payment_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="project_id" id="import_project_id">
                        <input type="hidden" name="payment_month" id="import_payment_month">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Process Bulk Payments
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync project and month selection for import
    const projectSelect = document.querySelector('select[name="project_id"]');
    const monthInput = document.querySelector('input[name="payment_month"]');
    
    projectSelect.addEventListener('change', updateImportFields);
    monthInput.addEventListener('change', updateImportFields);
    
    function updateImportFields() {
        document.getElementById('import_project_id').value = projectSelect.value;
        document.getElementById('import_payment_month').value = monthInput.value;
    }
    
    updateImportFields(); // Initial call
});
</script>
@endsection