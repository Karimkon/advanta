{{-- resources/views/finance/labor/create.blade.php --}}
@extends('finance.layouts.app')

@section('title', 'Add Labor Worker')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add New Labor Worker</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('finance.labor.index') }}">Labor Workers</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('finance.labor.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('finance.labor.store') }}" method="POST">
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

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="id_number" class="form-label">ID Number</label>
                        <input type="text" class="form-control" id="id_number" name="id_number" value="{{ old('id_number') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role/Position *</label>
                        <input type="text" class="form-control" id="role" name="role" value="{{ old('role') }}" 
                               placeholder="e.g., Mason, Carpenter, Helper" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_frequency" class="form-label">Payment Frequency *</label>
                        <select class="form-select" id="payment_frequency" name="payment_frequency" required>
                            <option value="">Select Frequency</option>
                            <option value="daily" {{ old('payment_frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ old('payment_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ old('payment_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" id="daily_rate_field">
                        <label for="daily_rate" class="form-label">Daily Rate (UGX) *</label>
                        <input type="number" step="0.01" class="form-control" id="daily_rate" name="daily_rate" 
                               value="{{ old('daily_rate') }}" min="0">
                    </div>

                    <div class="col-md-6 mb-3" id="monthly_rate_field" style="display: none;">
                        <label for="monthly_rate" class="form-label">Monthly Rate (UGX) *</label>
                        <input type="number" step="0.01" class="form-control" id="monthly_rate" name="monthly_rate" 
                               value="{{ old('monthly_rate') }}" min="0">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date *</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ old('start_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save Worker
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const frequencySelect = document.getElementById('payment_frequency');
    const dailyRateField = document.getElementById('daily_rate_field');
    const monthlyRateField = document.getElementById('monthly_rate_field');
    const dailyRateInput = document.getElementById('daily_rate');
    const monthlyRateInput = document.getElementById('monthly_rate');
    const form = document.querySelector('form');

    function toggleRateFields() {
        const frequency = frequencySelect.value;
        
        if (frequency === 'daily') {
            dailyRateField.style.display = 'block';
            monthlyRateField.style.display = 'none';
            dailyRateInput.required = true;
            monthlyRateInput.required = false;
            monthlyRateInput.value = '0'; // Set default for hidden field
        } else if (frequency === 'monthly') {
            dailyRateField.style.display = 'none';
            monthlyRateField.style.display = 'block';
            dailyRateInput.required = false;
            monthlyRateInput.required = true;
            dailyRateInput.value = '0'; // Set default for hidden field
        } else if (frequency === 'weekly') {
            dailyRateField.style.display = 'block';
            monthlyRateField.style.display = 'none';
            dailyRateInput.required = true;
            monthlyRateInput.required = false;
            monthlyRateInput.value = '0'; // Set default for hidden field
        }
    }

    // Ensure defaults are set before form submission
    form.addEventListener('submit', function(e) {
        const frequency = frequencySelect.value;
        
        if (frequency === 'daily' || frequency === 'weekly') {
            if (!monthlyRateInput.value) {
                monthlyRateInput.value = '0';
            }
        } else if (frequency === 'monthly') {
            if (!dailyRateInput.value) {
                dailyRateInput.value = '0';
            }
        }
        
        console.log('Form submission:', {
            frequency: frequency,
            daily_rate: dailyRateInput.value,
            monthly_rate: monthlyRateInput.value
        });
    });

    frequencySelect.addEventListener('change', toggleRateFields);
    toggleRateFields(); // Initial call
});
</script>
@endsection