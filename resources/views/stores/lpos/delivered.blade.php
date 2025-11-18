@extends('stores.layouts.app')

@section('title', 'Delivered LPOs - ' . $store->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle"></i> Delivered LPOs - {{ $store->name }}
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('stores.lpos.index', $store) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-clock-history"></i> Pending Delivery
                            </a>
                            <a href="{{ route('stores.dashboard') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($lpos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>LPO Number</th>
                                        <th>Supplier</th>
                                        <th>Project</th>
                                        <th>Items</th>
                                        <th>Delivery Date</th>
                                        <th>Received By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lpos as $lpo)
                                        <tr>
                                            <td>
                                                <strong>{{ $lpo->lpo_number }}</strong>
                                                <br>
                                                <small class="text-muted">Req: {{ $lpo->requisition->ref }}</small>
                                            </td>
                                            <td>{{ $lpo->supplier->name ?? 'N/A' }}</td>
                                            <td>{{ $lpo->requisition->project->name }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $lpo->items->count() }} items</span>
                                            </td>
                                            <td>{{ $lpo->delivery_date ? $lpo->delivery_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $lastApproval = $lpo->requisition->approvals()
                                                        ->where('action', 'delivery_confirmed')
                                                        ->latest()
                                                        ->first();
                                                @endphp
                                                {{ $lastApproval->approver->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <a href="{{ route('stores.lpos.show', ['store' => $store, 'lpo' => $lpo]) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $lpos->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">No Delivered LPOs</h4>
                            <p class="text-muted">LPOs that have been confirmed as delivered will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection