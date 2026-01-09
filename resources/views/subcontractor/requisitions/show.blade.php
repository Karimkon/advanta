@extends('subcontractor.layouts.app')

@section('title', 'Requisition ' . $requisition->ref)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $requisition->ref }}</h2>
            <p class="text-muted mb-0">
                <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }} me-2">
                    {{ ucfirst($requisition->type) }} Requisition
                </span>
                <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                    {{ $requisition->getCurrentStage() }}
                </span>
            </p>
        </div>
        <div>
            @if($requisition->canBeEdited())
                <a href="{{ route('subcontractor.requisitions.edit', $requisition) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endif
            <a href="{{ route('subcontractor.requisitions.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Requisition Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle text-primary me-2"></i>Requisition Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Project</label>
                            <p class="mb-0 fw-bold">{{ $requisition->project->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Type</label>
                            <p class="mb-0">
                                <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }}">
                                    {{ ucfirst($requisition->type) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Urgency</label>
                            <p class="mb-0">
                                <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                    {{ ucfirst($requisition->urgency) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Estimated Total</label>
                            <p class="mb-0 fw-bold text-success">UGX {{ number_format($requisition->estimated_total) }}</p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small">Reason / Justification</label>
                            <p class="mb-0">{{ $requisition->reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul text-info me-2"></i>Items ({{ $requisition->items->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->items as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->name }}</strong>
                                            @if($item->description)
                                                <br><small class="text-muted">{{ $item->description }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->estimated_unit_price) }}</td>
                                        <td class="fw-bold">UGX {{ number_format($item->quantity * $item->estimated_unit_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold text-success">UGX {{ number_format($requisition->estimated_total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Approval History -->
            @if($requisition->approvals->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history text-secondary me-2"></i>Approval History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($requisition->approvals as $approval)
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <span class="badge {{ $approval->action === 'approved' ? 'bg-success' : ($approval->action === 'rejected' ? 'bg-danger' : 'bg-info') }} rounded-circle p-2">
                                        <i class="bi {{ $approval->action === 'approved' ? 'bi-check' : ($approval->action === 'rejected' ? 'bi-x' : 'bi-arrow-right') }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1">
                                        <strong>{{ $approval->user->name ?? 'System' }}</strong>
                                        <span class="badge bg-secondary">{{ ucfirst($approval->role) }}</span>
                                        {{ $approval->action }}
                                    </p>
                                    @if($approval->comment)
                                        <small class="text-muted">{{ $approval->comment }}</small>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $approval->created_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header {{ $requisition->getStatusBadgeClass() }} text-white">
                    <h5 class="mb-0"><i class="bi bi-flag me-2"></i>Status</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-2">{{ $requisition->getCurrentStage() }}</h4>
                    <p class="text-muted mb-0">
                        @if($requisition->status === 'pending')
                            Your requisition is waiting for review.
                        @elseif($requisition->status === 'rejected')
                            This requisition was rejected.
                        @elseif($requisition->status === 'completed')
                            This requisition has been completed.
                        @else
                            Your requisition is being processed.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Workflow Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Workflow Progress</h6>
                </div>
                <div class="card-body">
                    @php
                        $workflow = $requisition->isStoreRequisition()
                            ? ['pending' => 'Submitted', 'project_manager_approved' => 'PM Approved', 'completed' => 'Store Released']
                            : ['pending' => 'Submitted', 'project_manager_approved' => 'PM Approved', 'operations_approved' => 'Ops Approved', 'procurement' => 'In Procurement', 'ceo_approved' => 'CEO Approved', 'lpo_issued' => 'LPO Issued', 'delivered' => 'Delivered', 'completed' => 'Completed'];

                        $currentFound = false;
                    @endphp

                    @foreach($workflow as $status => $label)
                        @php
                            $isCurrent = $requisition->status === $status;
                            $isPast = !$currentFound && !$isCurrent;
                            if ($isCurrent) $currentFound = true;
                        @endphp
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge {{ $isPast ? 'bg-success' : ($isCurrent ? 'bg-warning' : 'bg-secondary') }} me-2">
                                @if($isPast)
                                    <i class="bi bi-check"></i>
                                @elseif($isCurrent)
                                    <i class="bi bi-arrow-right"></i>
                                @else
                                    <i class="bi bi-circle"></i>
                                @endif
                            </span>
                            <small class="{{ $isCurrent ? 'fw-bold' : ($isPast ? '' : 'text-muted') }}">{{ $label }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Metadata -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Dates</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <span>{{ $requisition->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated:</span>
                        <span>{{ $requisition->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
