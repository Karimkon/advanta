@extends('admin.layouts.app')
@section('title', 'Subcontractor Details')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $subcontractor->name }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-{{ $subcontractor->status === 'active' ? 'success' : 'secondary' }}">
                    {{ ucfirst($subcontractor->status) }}
                </span>
                <span class="badge bg-info ms-1">{{ $subcontractor->specialization }}</span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.subcontractors.edit', $subcontractor) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.subcontractors.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Subcontractor Info Card -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Contact Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="width: 40%;">Contact Person</th>
                            <td>{{ $subcontractor->contact_person ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Phone</th>
                            <td>
                                @if($subcontractor->phone)
                                    <a href="tel:{{ $subcontractor->phone }}">{{ $subcontractor->phone }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td>
                                @if($subcontractor->email)
                                    <a href="mailto:{{ $subcontractor->email }}">{{ $subcontractor->email }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Tax Number</th>
                            <td>{{ $subcontractor->tax_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Address</th>
                            <td>{{ $subcontractor->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Last Login</th>
                            <td>{{ $subcontractor->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Financial Summary -->
            @php
                $totalContractValue = $subcontractor->projectSubcontractors->sum('contract_amount');
                $totalPaid = $subcontractor->projectSubcontractors->flatMap->payments->sum('amount');
                $balance = $totalContractValue - $totalPaid;
            @endphp
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash"></i> Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Contract Value:</span>
                        <strong>KES {{ number_format($totalContractValue, 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid:</span>
                        <strong class="text-success">KES {{ number_format($totalPaid, 0) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Outstanding Balance:</span>
                        <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                            KES {{ number_format($balance, 0) }}
                        </strong>
                    </div>
                    <div class="progress mt-3" style="height: 10px;">
                        @php $percentage = $totalContractValue > 0 ? ($totalPaid / $totalContractValue) * 100 : 0; @endphp
                        <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                    </div>
                    <small class="text-muted">{{ number_format($percentage, 1) }}% paid</small>
                </div>
            </div>
        </div>

        <!-- Contracts & Payments -->
        <div class="col-md-8">
            <!-- Add Contract Button -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Project Contracts</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addContractModal">
                    <i class="bi bi-plus"></i> Add Contract
                </button>
            </div>

            @forelse($subcontractor->projectSubcontractors as $contract)
                @php
                    $contractPaid = $contract->payments->sum('amount');
                    $contractBalance = $contract->contract_amount - $contractPaid;
                @endphp
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $contract->contract_number }}</strong>
                            <span class="badge bg-{{ $contract->status === 'active' ? 'success' : ($contract->status === 'completed' ? 'info' : 'danger') }} ms-2">
                                {{ ucfirst($contract->status) }}
                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.subcontractors.contracts.edit', ['projectSubcontractor' => $contract->id]) }}" class="btn btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.subcontractors.contracts.destroy', ['projectSubcontractor' => $contract->id]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this contract and all related data?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Project:</strong> {{ $contract->project->name ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Work:</strong> {{ $contract->work_description }}</p>
                                <p class="mb-1"><strong>Duration:</strong> {{ $contract->start_date?->format('M d, Y') }} - {{ $contract->end_date?->format('M d, Y') ?? 'Ongoing' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Contract Amount:</strong> KES {{ number_format($contract->contract_amount, 0) }}</p>
                                <p class="mb-1"><strong>Paid:</strong> <span class="text-success">KES {{ number_format($contractPaid, 0) }}</span></p>
                                <p class="mb-0"><strong>Balance:</strong> <span class="{{ $contractBalance > 0 ? 'text-danger' : 'text-success' }}">KES {{ number_format($contractBalance, 0) }}</span></p>
                            </div>
                        </div>

                        <!-- Payment History -->
                        @if($contract->payments->count() > 0)
                            <hr>
                            <h6><i class="bi bi-receipt"></i> Payment History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Paid By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($contract->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date?->format('M d, Y') }}</td>
                                                <td>{{ $payment->payment_reference }}</td>
                                                <td><span class="badge bg-secondary">{{ ucfirst($payment->payment_type) }}</span></td>
                                                <td class="text-success">KES {{ number_format($payment->amount, 0) }}</td>
                                                <td>{{ $payment->paidBy->name ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <hr>
                            <p class="text-muted mb-0"><i class="bi bi-info-circle"></i> No payments recorded for this contract.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-file-text display-4 text-muted d-block mb-3"></i>
                        <p class="text-muted">No contracts assigned to this subcontractor.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContractModal">
                            <i class="bi bi-plus"></i> Add First Contract
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Add Contract Modal -->
<div class="modal fade" id="addContractModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.subcontractors.contracts.add', $subcontractor) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Contract</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contract Amount (KES) <span class="text-danger">*</span></label>
                            <input type="number" name="contract_amount" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Work Description <span class="text-danger">*</span></label>
                            <textarea name="work_description" class="form-control" rows="3" required placeholder="Describe the work to be done..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="2" placeholder="Any specific terms..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Add Contract
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize projects variable for the modal
    const projects = @json($projects ?? []);
</script>
@endpush
@endsection
