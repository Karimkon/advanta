@extends('procurement.layouts.app')

@section('title', 'LPO Management - Procurement')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">LPO Management</h2>
            <p class="text-muted mb-0">Manage all Local Purchase Orders</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.requisitions.pending') }}" class="btn btn-warning">
                <i class="bi bi-clock"></i> Pending Requisitions
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($lpos->pluck('supplier')->unique() as $supplier)
                            @if($supplier)
                                <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                    <a href="{{ route('procurement.lpos.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- LPOs Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>LPO Number</th>
                            <th>Requisition Ref</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lpos as $lpo)
                            <tr>
                                <td>
                                    <strong>{{ $lpo->lpo_number }}</strong>
                                </td>
                                <td>{{ $lpo->requisition->ref }}</td>
                                <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                                <td>UGX {{ number_format($lpo->items->sum('total_price'), 2) }}</td>
                                <td>{{ $lpo->delivery_date ? $lpo->delivery_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $lpo->status === 'issued' ? 'success' : ($lpo->status === 'delivered' ? 'info' : 'warning') }}">
                                        {{ ucfirst($lpo->status) }}
                                    </span>
                                </td>
                                <td>{{ $lpo->created_at->format('M d, Y') }}</td>
                                <td>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('procurement.lpos.show', $lpo) }}" 
           class="btn btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
        </a>
        @if($lpo->status === 'draft' && $lpo->requisition->status === \App\Models\Requisition::STATUS_CEO_APPROVED)
            <form action="{{ route('procurement.lpos.issue', $lpo) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm"
                        onclick="return confirm('Issue this LPO to supplier?')">
                    <i class="bi bi-send"></i>
                </button>
            </form>
        @endif
    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-receipt display-4 d-block mb-2"></i>
                                        No LPOs found.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($lpos->hasPages())
                <div class="card-footer bg-white">
                    {{ $lpos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelects = document.querySelectorAll('select[name="status"], select[name="supplier_id"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
</script>
@endpush