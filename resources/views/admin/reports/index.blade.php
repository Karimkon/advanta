@extends('admin.layouts.app')
@section('title', 'Reports & Analytics')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Reports & Analytics</h2>
            <p class="text-muted mb-0">System-wide reports and analytics dashboard</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_projects'] }}</h3>
                            <small>Total Projects</small>
                        </div>
                        <i class="bi bi-folder fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_requisitions'] }}</h3>
                            <small>Total Requisitions</small>
                        </div>
                        <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_lpos'] }}</h3>
                            <small>Total LPOs</small>
                        </div>
                        <i class="bi bi-receipt fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ number_format($stats['total_payments'], 0) }}</h3>
                            <small>Payments (UGX)</small>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Projects Summary -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-folder me-2"></i>Projects Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $stats['active_projects'] }}</h4>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $stats['completed_projects'] }}</h4>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $stats['on_hold_projects'] }}</h4>
                                <small class="text-muted">On Hold</small>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Budget:</span>
                            <strong>UGX {{ number_format($stats['total_budget'], 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requisitions Summary -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Requisitions Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <h5 class="text-warning mb-1">{{ $stats['pending_requisitions'] }}</h5>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <h5 class="text-info mb-1">{{ $stats['approved_requisitions'] }}</h5>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <h5 class="text-success mb-1">{{ $stats['completed_requisitions'] }}</h5>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <h5 class="text-danger mb-1">{{ $stats['rejected_requisitions'] }}</h5>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Estimated Value:</span>
                            <strong>UGX {{ number_format($stats['requisitions_value'], 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LPO Summary -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>LPO Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-1">{{ $stats['issued_lpos'] }}</h4>
                                <small class="text-muted">Issued</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $stats['delivered_lpos'] }}</h4>
                                <small class="text-muted">Delivered</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-1">{{ $stats['cancelled_lpos'] }}</h4>
                                <small class="text-muted">Cancelled</small>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total LPO Value:</span>
                            <strong>UGX {{ number_format($stats['lpos_value'], 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments Summary -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payments Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $stats['pending_payments'] }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $stats['approved_payments'] }}</h4>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-1">{{ $stats['rejected_payments'] }}</h4>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Paid:</span>
                            <strong>UGX {{ number_format($stats['total_paid'], 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Summary -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-boxes me-2"></i>Inventory Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $stats['total_stores'] }}</h4>
                                <small class="text-muted">Stores</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-1">{{ $stats['total_inventory_items'] }}</h4>
                                <small class="text-muted">Items</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $stats['low_stock_items'] }}</h4>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Inventory Value:</span>
                            <strong>UGX {{ number_format($stats['inventory_value'], 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suppliers Summary -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Suppliers Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $stats['total_suppliers'] }}</h4>
                                <small class="text-muted">Total Suppliers</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ $stats['active_suppliers'] }}</h4>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Requisitions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentRequisitions as $req)
                            <a href="{{ route('admin.requisitions.show', $req) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $req->ref }}</strong>
                                        <br><small class="text-muted">{{ $req->project->name ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $req->status === 'completed' ? 'success' : ($req->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                        <br><small class="text-muted">{{ $req->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                No recent requisitions
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent LPOs</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentLpos as $lpo)
                            <a href="{{ route('admin.lpos.show', $lpo) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $lpo->lpo_number }}</strong>
                                        <br><small class="text-muted">{{ $lpo->supplier->name ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $lpo->status === 'delivered' ? 'success' : ($lpo->status === 'cancelled' ? 'danger' : 'info') }}">
                                            {{ ucfirst($lpo->status) }}
                                        </span>
                                        <br><small class="text-muted">UGX {{ number_format($lpo->total, 0) }}</small>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                No recent LPOs
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
