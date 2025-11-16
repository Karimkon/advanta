@extends('engineer.layouts.app')

@section('title', 'Requisition Details - ' . $requisition->ref)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Requisition Details - {{ $requisition->ref }}</h5>
                    <div class="btn-group">
                        <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="140">Reference:</th>
                                    <td><strong>{{ $requisition->ref }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Project:</th>
                                    <td>{{ $requisition->project->name }}</td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        <span class="badge {{ $requisition->type === 'store' ? 'bg-info' : 'bg-primary' }}">
                                            {{ ucfirst($requisition->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge {{ $requisition->getStatusBadgeClass() }}">
                                            {{ $requisition->getCurrentStage() }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Details</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="140">Urgency:</th>
                                    <td>
                                        <span class="badge bg-{{ $requisition->urgency === 'high' ? 'danger' : ($requisition->urgency === 'medium' ? 'warning' : 'success') }}">
                                            {{ ucfirst($requisition->urgency) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td><strong>UGX {{ number_format($requisition->estimated_total, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $requisition->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td>{{ $requisition->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($requisition->reason)
                    <div class="mt-3">
                        <h6>Reason/Purpose</h6>
                        <p class="text-muted">{{ $requisition->reason }}</p>
                    </div>
                    @endif

                    @if($requisition->store)
                    <div class="mt-3">
                        <h6>Store Information</h6>
                        <p class="text-muted">
                            <i class="bi bi-shop"></i> {{ $requisition->store->name }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Requisition Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->name }}
                                            @if($item->from_store)
                                                <span class="badge bg-success ms-1">From Store</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td><strong>UGX {{ number_format($item->total_price, 2) }}</strong></td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                    <td colspan="2"><strong>UGX {{ number_format($requisition->estimated_total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Approval History -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Approval History</h6>
                </div>
                <div class="card-body">
                    @if($requisition->approvals->count() > 0)
                        <div class="timeline">
                            @foreach($requisition->approvals as $approval)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="bg-{{ $approval->action === 'rejected' ? 'danger' : 'success' }} rounded-circle p-2">
                                                <i class="bi bi-{{ $approval->action === 'rejected' ? 'x' : 'check' }}-circle text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">
                                                {{ $approval->approver->name }}
                                                <small class="text-muted">({{ ucfirst($approval->role) }})</small>
                                            </h6>
                                            <p class="mb-1 small">{{ $approval->comment }}</p>
                                            <small class="text-muted">{{ $approval->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-clock-history display-4 d-block mb-2"></i>
                            No approval history yet
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('engineer.requisitions.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-list-ul"></i> All Requisitions
                        </a>
                        <a href="{{ route('engineer.requisitions.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Requisition
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection