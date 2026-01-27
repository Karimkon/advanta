@extends('admin.layouts.app')
@section('title', 'Edit Contract')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Contract</h2>
            <p class="text-muted mb-0">{{ $contract->contract_number }} - {{ $contract->subcontractor->name }}</p>
        </div>
        <a href="{{ route('admin.subcontractors.show', $contract->subcontractor_id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Subcontractor
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.subcontractors.contracts.update', ['projectSubcontractor' => $contract->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contract Number</label>
                                <input type="text" class="form-control" value="{{ $contract->contract_number }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Project</label>
                                <input type="text" class="form-control" value="{{ $contract->project->name ?? 'N/A' }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contract Amount (KES) <span class="text-danger">*</span></label>
                                <input type="number" name="contract_amount" class="form-control @error('contract_amount') is-invalid @enderror"
                                       value="{{ old('contract_amount', $contract->contract_amount) }}" required min="0" step="0.01">
                                @error('contract_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $contract->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ old('status', $contract->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="terminated" {{ old('status', $contract->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Work Description <span class="text-danger">*</span></label>
                                <textarea name="work_description" class="form-control @error('work_description') is-invalid @enderror" rows="4" required>{{ old('work_description', $contract->work_description) }}</textarea>
                                @error('work_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Terms & Conditions</label>
                                <textarea name="terms" class="form-control @error('terms') is-invalid @enderror" rows="3">{{ old('terms', $contract->terms) }}</textarea>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.subcontractors.show', $contract->subcontractor_id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Contract
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash"></i> Payment Summary</h5>
                </div>
                <div class="card-body">
                    @php
                        $totalPaid = $contract->payments->sum('amount');
                        $balance = $contract->contract_amount - $totalPaid;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span>Contract Amount:</span>
                        <strong>KES {{ number_format($contract->contract_amount, 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid:</span>
                        <strong class="text-success">KES {{ number_format($totalPaid, 0) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Balance:</span>
                        <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                            KES {{ number_format($balance, 0) }}
                        </strong>
                    </div>
                </div>
            </div>

            @if($contract->payments->count() > 0)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-receipt"></i> Payment History</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($contract->payments as $payment)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $payment->payment_date?->format('M d, Y') }}</small>
                                    <strong class="text-success">KES {{ number_format($payment->amount, 0) }}</strong>
                                </div>
                                <small>{{ $payment->payment_reference }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
