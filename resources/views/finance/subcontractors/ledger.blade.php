{{-- resources/views/finance/subcontractors/ledger.blade.php --}}
@extends('finance.layouts.app')

@section('title', 'Ledger - ' . $projectSubcontractor->subcontractor->name)

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Premium Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                    <i class="bi bi-journal-text fs-4"></i>
                </div>
                <div>
                    <h1 class="fw-bold mb-1 text-dark fs-3">Payment Ledger</h1>
                    <p class="text-muted mb-0 fs-6">Complete transaction history and balance tracking</p>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent px-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('finance.dashboard') }}" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('finance.subcontractors.index') }}" class="text-decoration-none">Subcontractors</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('finance.subcontractors.show', $projectSubcontractor->subcontractor) }}" 
                           class="text-decoration-none">
                            {{ $projectSubcontractor->subcontractor->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active fw-semibold">Ledger</li>
                </ol>
            </nav>
        </div>

        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="{{ route('finance.subcontractors.show', $projectSubcontractor->subcontractor) }}" 
                   class="btn btn-light border shadow-sm px-4 py-2">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </a>
                <a href="{{ route('finance.subcontractors.payments.create', $projectSubcontractor) }}" 
                   class="btn btn-success shadow-sm px-4 py-2" 
                   {{ $projectSubcontractor->balance <= 0 ? 'disabled' : '' }}>
                    <i class="bi bi-cash-coin me-2"></i>Record Payment
                </a>
            </div>
        </div>
    </div>

    <!-- Premium Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10">
                    <i class="bi bi-file-earmark-text" style="font-size: 80px; margin-top: -10px; margin-right: -10px;"></i>
                </div>
                <div class="card-body p-3 position-relative">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                            <i class="bi bi-file-earmark-text text-primary"></i>
                        </div>
                        <h6 class="text-uppercase text-muted fw-semibold mb-0 small tracking-wide">Contract Amount</h6>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">UGX {{ number_format($projectSubcontractor->contract_amount, 2) }}</h3>
                    <p class="text-muted small mb-0">Total contracted value</p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 px-3 pb-3">
                    <div class="progress" style="height: 3px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10">
                    <i class="bi bi-check-circle" style="font-size: 80px; margin-top: -10px; margin-right: -10px;"></i>
                </div>
                <div class="card-body p-3 position-relative">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                        <h6 class="text-uppercase text-muted fw-semibold mb-0 small tracking-wide">Total Paid</h6>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">UGX {{ number_format($projectSubcontractor->total_paid, 2) }}</h3>
                    <p class="text-muted small mb-0">Payments completed</p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 px-3 pb-3">
                    <div class="progress" style="height: 3px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $projectSubcontractor->completion_percentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 opacity-10">
                    <i class="bi bi-wallet2" style="font-size: 80px; margin-top: -10px; margin-right: -10px;"></i>
                </div>
                <div class="card-body p-3 position-relative">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rounded-circle {{ $projectSubcontractor->balance > 0 ? 'bg-warning' : 'bg-info' }} bg-opacity-10 p-2 me-2">
                            <i class="bi bi-wallet2 {{ $projectSubcontractor->balance > 0 ? 'text-warning' : 'text-info' }}"></i>
                        </div>
                        <h6 class="text-uppercase text-muted fw-semibold mb-0 small tracking-wide">Balance</h6>
                    </div>
                    <h3 class="fw-bold {{ $projectSubcontractor->balance > 0 ? 'text-warning' : 'text-success' }} mb-1">
                        UGX {{ number_format($projectSubcontractor->balance, 2) }}
                    </h3>
                    <p class="text-muted small mb-0">
                        {{ $projectSubcontractor->balance > 0 ? 'Amount remaining' : 'Fully paid' }}
                    </p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 px-3 pb-3">
                    <div class="progress" style="height: 3px;">
                        <div class="progress-bar {{ $projectSubcontractor->balance > 0 ? 'bg-warning' : 'bg-info' }}" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Details Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-briefcase text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-2">{{ $projectSubcontractor->work_description }}</h5>
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                <span>
                                    <i class="bi bi-building me-1"></i>
                                    <strong class="text-dark">{{ $projectSubcontractor->project->name }}</strong>
                                </span>
                                <span class="border-start ps-3">
                                    <i class="bi bi-person-badge me-1"></i>
                                    <strong class="text-dark">{{ $projectSubcontractor->subcontractor->name }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 bg-light rounded-pill">
                        <span class="badge bg-{{ $projectSubcontractor->balance > 0 ? 'warning' : 'success' }} rounded-circle p-2">
                            <i class="bi bi-{{ $projectSubcontractor->balance > 0 ? 'clock-history' : 'check-lg' }}"></i>
                        </span>
                        <span class="fw-semibold small">
                            {{ $projectSubcontractor->balance > 0 ? 'In Progress' : 'Completed' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Ledger Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-4 px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Transaction History</h5>
                    <p class="text-muted small mb-0">Complete ledger of all debits and credits</p>
                </div>
                <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                    {{ count($ledger) }} {{ Str::plural('Entry', count($ledger)) }}
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small">Date</th>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small">Description</th>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small">Reference</th>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small">Type</th>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small text-end">Debit (UGX)</th>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small text-end">Credit (UGX)</th>
                            <th class="px-4 py-3 text-uppercase text-muted fw-semibold small text-end">Balance (UGX)</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ledger as $entry)
                            <tr class="border-bottom {{ $entry['type'] === 'contract' ? 'bg-primary bg-opacity-5' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar3 text-muted"></i>
                                        <span class="fw-semibold text-dark">{{ $entry['date']->format('M d, Y') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="text-dark">{{ $entry['description'] }}</span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 fw-normal">
                                        {{ $entry['reference'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="badge bg-{{ $entry['type'] === 'contract' ? 'primary' : 'success' }} px-3 py-2">
                                        <i class="bi bi-{{ $entry['type'] === 'contract' ? 'file-earmark-text' : 'cash-coin' }} me-1"></i>
                                        {{ ucfirst($entry['type']) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-end">
                                    @if($entry['debit'] > 0)
                                        <span class="fw-bold text-success">
                                            + {{ number_format($entry['debit'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-end">
                                    @if($entry['credit'] > 0)
                                        <span class="fw-bold text-danger">
                                            − {{ number_format($entry['credit'], 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-end">
                                    <span class="badge bg-{{ $entry['balance'] > 0 ? 'warning' : 'success' }} bg-opacity-10 text-{{ $entry['balance'] > 0 ? 'warning' : 'success' }} px-3 py-2 fw-bold border border-{{ $entry['balance'] > 0 ? 'warning' : 'success' }} border-opacity-25">
                                        {{ number_format($entry['balance'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Premium Completion Status Card -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h6 class="fw-bold text-uppercase text-muted small mb-3 tracking-wide">
                        <i class="bi bi-graph-up me-2"></i>Contract Completion Status
                    </h6>
                    
                    <div class="position-relative mb-3">
                        <div class="progress shadow-sm" style="height: 30px; border-radius: 10px; background-color: #e9ecef;">
                            <div class="progress-bar-custom text-white fw-bold d-flex align-items-center justify-content-center"
                                 role="progressbar"
                                 style="width: {{ $projectSubcontractor->completion_percentage }}%;"
                                 aria-valuenow="{{ $projectSubcontractor->completion_percentage }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                 <span class="px-2">{{ number_format($projectSubcontractor->completion_percentage, 1) }}% Complete</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between text-muted small">
                        <span>
                            <i class="bi bi-calendar-check me-1"></i>
                            Started: <strong class="text-dark">{{ $projectSubcontractor->start_date->format('M d, Y') }}</strong>
                        </span>
                        <span>
                            @if($projectSubcontractor->end_date)
                                <i class="bi bi-calendar-check-fill me-1"></i>
                                Completed: <strong class="text-dark">{{ $projectSubcontractor->end_date->format('M d, Y') }}</strong>
                            @else
                                <i class="bi bi-clock-history me-1"></i>
                                <strong class="text-warning">In Progress</strong>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="col-lg-4 text-center mt-4 mt-lg-0">
                    <div class="position-relative d-inline-block">
                        <svg width="140" height="140" style="transform: rotate(-90deg);">
                            <circle cx="70" cy="70" r="60" stroke="#e9ecef" stroke-width="12" fill="none"></circle>
                            <circle cx="70" cy="70" r="60" 
                                    stroke="{{ $projectSubcontractor->completion_percentage >= 100 ? '#198754' : '#ffc107' }}" 
                                    stroke-width="12" 
                                    fill="none"
                                    stroke-dasharray="{{ 2 * 3.14159 * 60 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 60 * (1 - $projectSubcontractor->completion_percentage / 100) }}"
                                    stroke-linecap="round"
                                    style="transition: stroke-dashoffset 0.5s ease;">
                            </circle>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h3 class="fw-bold mb-0 {{ $projectSubcontractor->completion_percentage >= 100 ? 'text-success' : 'text-warning' }}">
                                {{ number_format($projectSubcontractor->completion_percentage, 0) }}%
                            </h3>
                            <small class="text-muted">Paid</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.tracking-wide {
    letter-spacing: 0.05em;
}

.icon-box {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
    transition: background-color 0.2s ease;
}

.btn {
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.badge {
    font-weight: 500;
    letter-spacing: 0.02em;
}

.breadcrumb-item a {
    color: #6c757d;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}

.progress-bar-custom {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, #198754 0%, #20c997 100%);
    transition: width 0.6s ease;
}
</style>
@endsection