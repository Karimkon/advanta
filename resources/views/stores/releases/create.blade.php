@extends('stores.layouts.app')

@section('title', 'Process Store Release - ' . $store->display_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Process Store Release</h2>
            <p class="text-muted mb-0">Release items for requisition: <strong>{{ $requisition->ref }}</strong></p>
        </div>
        <a href="{{ route('stores.releases.index', $store) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Releases
        </a>
    </div>

    <form action="{{ route('stores.releases.store', [$store, $requisition]) }}" method="POST">
        @csrf
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Requisition Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Project:</strong> {{ $requisition->project->name }}</p>
                        <p><strong>Requested By:</strong> {{ $requisition->requester->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Urgency:</strong> 
                            <span class="badge {{ $requisition->getUrgencyBadgeClass() }}">
                                {{ ucfirst($requisition->urgency) }}
                            </span>
                        </p>
                        <p><strong>Date:</strong> {{ $requisition->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Items to Release</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Requested Qty</th>
                                <th>Available Stock</th>
                                <th>Quantity to Release</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requisition->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->notes)
                                            <br><small class="text-muted">{{ $item->notes }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>
                                        <span class="badge {{ $item->can_fulfill ? 'bg-success' : 'bg-danger' }}">
                                            {{ $item->available_stock }} {{ $item->unit }}
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="items[{{ $item->id }}][quantity_released]" 
                                               class="form-control" 
                                               value="{{ min($item->quantity, $item->available_stock) }}"
                                               max="{{ $item->available_stock }}"
                                               min="0"
                                               step="0.01"
                                               required>
                                    </td>
                                    <td>
                                        @if($item->can_fulfill)
                                            <span class="badge bg-success">Can Fulfill</span>
                                        @else
                                            <span class="badge bg-danger">Insufficient Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Release Notes (Optional)</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Add any notes about this release..."></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('stores.releases.index', $store) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Complete Release
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection