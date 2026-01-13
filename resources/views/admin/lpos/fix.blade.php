@extends('admin.layouts.app')
@section('title', 'Fix LPO ' . $lpo->lpo_number)
@section('content')
<div class="container">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between mb-3">
        <div>
            <h4>Fix LPO {{ $lpo->lpo_number }}</h4>
            <p class="text-muted mb-0">Manually adjust status for this LPO and its requisition</p>
        </div>
        <div>
            <a href="{{ route('admin.lpos.show', $lpo) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to LPO
            </a>
        </div>
    </div>

    <div class="row">
        <!-- LPO Info -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> LPO Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">LPO Number:</td>
                            <td><strong>{{ $lpo->lpo_number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Supplier:</td>
                            <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total:</td>
                            <td><strong>UGX {{ number_format($lpo->total, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current Status:</td>
                            <td>
                                <span class="badge bg-{{ $lpo->status === 'delivered' ? 'success' : ($lpo->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $lpo->status)) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Requisition Info -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Requisition Details</h5>
                </div>
                <div class="card-body">
                    @if($lpo->requisition)
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Reference:</td>
                            <td><strong>{{ $lpo->requisition->ref }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Project:</td>
                            <td>{{ $lpo->requisition->project->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Requested By:</td>
                            <td>{{ $lpo->requisition->requester->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current Status:</td>
                            <td>
                                <span class="badge {{ $lpo->requisition->getStatusBadgeClass() }}">
                                    {{ $lpo->requisition->getCurrentStage() }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    @else
                    <p class="text-muted mb-0">No linked requisition</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Fix Form -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="bi bi-tools"></i> Fix Status</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Warning:</strong> Use this tool only to fix incorrect statuses. Changes are logged.
            </div>

            <form action="{{ route('admin.lpos.fix-status', $lpo) }}" method="POST">
                @csrf

                <div class="row">
                    <!-- LPO Status -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><strong>LPO Status</strong></label>
                            <select name="lpo_status" class="form-select">
                                <option value="">-- Keep Current --</option>
                                <option value="draft" {{ $lpo->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="issued" {{ $lpo->status === 'issued' ? 'selected' : '' }}>Issued</option>
                                <option value="sent" {{ $lpo->status === 'sent' ? 'selected' : '' }}>Sent to Supplier</option>
                                <option value="acknowledged" {{ $lpo->status === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                                <option value="partially_delivered" {{ $lpo->status === 'partially_delivered' ? 'selected' : '' }}>Partially Delivered</option>
                                <option value="delivered" {{ $lpo->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $lpo->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Requisition Status -->
                    @if($lpo->requisition)
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label"><strong>Requisition Status</strong></label>
                            <select name="requisition_status" class="form-select">
                                <option value="">-- Keep Current --</option>
                                <option value="pending" {{ $lpo->requisition->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="project_manager_approved" {{ $lpo->requisition->status === 'project_manager_approved' ? 'selected' : '' }}>PM Approved</option>
                                <option value="operations_approved" {{ $lpo->requisition->status === 'operations_approved' ? 'selected' : '' }}>Operations Approved</option>
                                <option value="procurement" {{ $lpo->requisition->status === 'procurement' ? 'selected' : '' }}>Procurement</option>
                                <option value="ceo_approved" {{ $lpo->requisition->status === 'ceo_approved' ? 'selected' : '' }}>CEO Approved</option>
                                <option value="lpo_issued" {{ $lpo->requisition->status === 'lpo_issued' ? 'selected' : '' }}>LPO Issued</option>
                                <option value="delivered" {{ $lpo->requisition->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="payment_completed" {{ $lpo->requisition->status === 'payment_completed' ? 'selected' : '' }}>Payment Completed</option>
                                <option value="completed" {{ $lpo->requisition->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ $lpo->requisition->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.lpos.show', $lpo) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Apply Fix
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
