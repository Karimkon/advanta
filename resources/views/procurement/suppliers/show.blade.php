@extends('procurement.layouts.app')

@section('title', $supplier->name . ' - Supplier Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $supplier->name }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('procurement.suppliers.index') }}">Suppliers</a></li>
                    <li class="breadcrumb-item active">{{ $supplier->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a href="{{ route('procurement.suppliers.edit', $supplier) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Supplier Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Supplier Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Supplier Code:</strong></td>
                                    <td>{{ $supplier->code }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Person:</strong></td>
                                    <td>{{ $supplier->contact_person }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>{{ $supplier->phone }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Category:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $supplier->category }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Rating:</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="text-warning me-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $supplier->rating ? '-fill' : '' }}"></i>
                                                @endfor
                                            </span>
                                            <span class="text-muted">({{ number_format($supplier->rating, 1) }})</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($supplier->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $supplier->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong>Address:</strong>
                        <p class="mt-1">{{ $supplier->address }}</p>
                    </div>

                    @if($supplier->notes)
                    <div class="mt-3">
                        <strong>Additional Notes:</strong>
                        <p class="mt-1">{{ $supplier->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Supplier LPO History -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">LPO History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>LPO Number</th>
                                    <th>Requisition</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplier->lpos as $lpo)
                                    <tr>
                                        <td>
                                            <strong>{{ $lpo->lpo_number }}</strong>
                                        </td>
                                        <td>{{ $lpo->requisition->ref ?? 'N/A' }}</td>
                                        <td>{{ $lpo->requisition->project->name ?? 'N/A' }}</td>
                                        <td>UGX {{ number_format($lpo->total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $lpo->status === 'issued' ? 'success' : ($lpo->status === 'delivered' ? 'info' : 'warning') }}">
                                                {{ ucfirst($lpo->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $lpo->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('procurement.lpos.show', $lpo) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-receipt display-4 d-block mb-2"></i>
                                            No LPOs found for this supplier.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Supplier Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{{ $supplier->lpos->count() }}</div>
                            <div class="stat-label">Total LPOs</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $supplier->lpos->where('status', 'issued')->count() }}</div>
                            <div class="stat-label">Active LPOs</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">UGX</div>
                            <div class="stat-label">{{ number_format($supplier->lpos->sum('total'), 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('procurement.suppliers.edit', $supplier) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Supplier
                        </a>
                        
                        @if($supplier->lpos->count() === 0)
                            <form action="{{ route('procurement.suppliers.destroy', $supplier) }}" method="POST" class="d-grid">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Delete this supplier? This action cannot be undone.')">
                                    <i class="bi bi-trash"></i> Delete Supplier
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger" disabled title="Cannot delete supplier with existing LPOs">
                                <i class="bi bi-trash"></i> Delete Supplier
                            </button>
                            <small class="text-muted text-center">Cannot delete supplier with existing LPOs</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <p class="mb-2">
                            <i class="bi bi-person me-2"></i>
                            <strong>{{ $supplier->contact_person }}</strong>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    text-align: center;
}

.stat-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.stat-value {
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
}

.contact-info i {
    width: 20px;
    text-align: center;
}
</style>
@endsection