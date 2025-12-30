@extends('admin.layouts.app')
@section('title', 'LPOs Management')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Local Purchase Orders (LPOs)</h2>
            <p class="text-muted mb-0">Manage all LPOs across the system</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-file-text text-primary fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $lpos->count() }}</h3>
                            <small class="text-muted">Total LPOs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-clock text-warning fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $lpos->whereIn('status', ['issued', 'pending', 'sent'])->count() }}</h3>
                            <small class="text-muted">Pending Delivery</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $lpos->where('status', 'delivered')->count() }}</h3>
                            <small class="text-muted">Delivered</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="bi bi-currency-dollar text-info fs-4"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ number_format($lpos->sum('total'), 0) }}</h3>
                            <small class="text-muted">Total Value (UGX)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">All LPOs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>LPO Number</th>
                            <th>Supplier</th>
                            <th>Project</th>
                            <th>Date</th>
                            <th>Total (UGX)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lpos as $lpo)
                            <tr>
                                <td>
                                    <strong>{{ $lpo->lpo_number }}</strong>
                                </td>
                                <td>
                                    @if($lpo->supplier)
                                        {{ $lpo->supplier->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lpo->requisition && $lpo->requisition->project)
                                        <a href="{{ route('admin.projects.show', $lpo->requisition->project) }}" class="text-decoration-none">
                                            {{ $lpo->requisition->project->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <small>{{ $lpo->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ number_format($lpo->total, 0) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'issued' => 'info',
                                            'sent' => 'primary',
                                            'delivered' => 'success',
                                            'invoiced' => 'dark',
                                            'paid' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $color = $statusColors[$lpo->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ ucfirst($lpo->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.lpos.show', $lpo) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-file-text display-4 d-block mb-2"></i>
                                    No LPOs issued yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
