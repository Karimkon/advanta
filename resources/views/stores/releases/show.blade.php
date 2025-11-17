@extends('stores.layouts.app')

@section('title', 'Store Release Details - ' . $store->display_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Store Release Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('stores.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('stores.releases.index', $store) }}">Store Releases</a></li>
                    <li class="breadcrumb-item active">Release #{{ $release->id }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('stores.releases.index', $store) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Releases
        </a>
    </div>

    <!-- Release Summary -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Release Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Release ID:</th>
                                    <td><strong>#{{ $release->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Requisition:</th>
                                    <td>{{ $release->requisition->ref }}</td>
                                </tr>
                                <tr>
                                    <th>Project:</th>
                                    <td>{{ $release->requisition->project->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($release->status) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Released By:</th>
                                    <td>{{ $release->releasedBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Release Date:</th>
                                    <td>{{ $release->released_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $release->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($release->notes)
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="mb-2">Release Notes:</h6>
                        <p class="mb-0">{{ $release->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="text-primary">{{ $release->items->count() }}</h3>
                        <p class="text-muted mb-0">Items Released</p>
                    </div>
                    <hr>
                    <div class="text-center">
                        @php
                            $totalQuantity = $release->items->sum('quantity_released');
                            $totalValue = 0;
                            foreach($release->items as $item) {
                                if($item->inventoryItem) {
                                    $totalValue += $item->quantity_released * $item->inventoryItem->unit_price;
                                }
                            }
                        @endphp
                        <h3 class="text-success">{{ $totalQuantity }}</h3>
                        <p class="text-muted mb-0">Total Quantity</p>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h3 class="text-info">UGX {{ number_format($totalValue, 2) }}</h3>
                        <p class="text-muted mb-0">Total Value</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Released Items -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Released Items</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>Inventory Item</th>
                            <th>Quantity Requested</th>
                            <th>Quantity Released</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($release->items as $item)
                            @php
                                $itemValue = $item->inventoryItem ? $item->quantity_released * $item->inventoryItem->unit_price : 0;
                                $isFullySatisfied = $item->quantity_released >= $item->quantity_requested;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $item->requisitionItem->name ?? 'N/A' }}</strong>
                                    @if($item->requisitionItem && $item->requisitionItem->notes)
                                        <br><small class="text-muted">{{ $item->requisitionItem->notes }}</small>
                                    @endif
                                    @if($item->notes)
                                        <br><small class="text-info"><i>Release note: {{ $item->notes }}</i></small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->inventoryItem)
                                        {{ $item->inventoryItem->name }}
                                        <br><small class="text-muted">SKU: {{ $item->inventoryItem->sku }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $item->quantity_requested }} {{ $item->requisitionItem->unit ?? '' }}</td>
                                <td>
                                    <strong>{{ $item->quantity_released }} {{ $item->requisitionItem->unit ?? '' }}</strong>
                                </td>
                                <td>
                                    @if($item->inventoryItem)
                                        UGX {{ number_format($item->inventoryItem->unit_price, 2) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->inventoryItem)
                                        <strong>UGX {{ number_format($itemValue, 2) }}</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isFullySatisfied)
                                        <span class="badge bg-success">Fully Satisfied</span>
                                    @else
                                        <span class="badge bg-warning">Partially Satisfied</span>
                                        <br><small class="text-muted">
                                            {{ $item->quantity_requested - $item->quantity_released }} remaining
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($release->items->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3">Totals</th>
                                <th><strong>{{ $release->items->sum('quantity_released') }}</strong></th>
                                <th></th>
                                <th><strong>UGX {{ number_format($totalValue, 2) }}</strong></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Requisition Information -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Original Requisition Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Requisition Reference:</strong> {{ $release->requisition->ref }}</p>
                    <p><strong>Project:</strong> {{ $release->requisition->project->name ?? 'N/A' }}</p>
                    <p><strong>Requested By:</strong> {{ $release->requisition->requester->name }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Urgency:</strong> 
                        <span class="badge {{ $release->requisition->getUrgencyBadgeClass() }}">
                            {{ ucfirst($release->requisition->urgency) }}
                        </span>
                    </p>
                    <p><strong>Requisition Date:</strong> {{ $release->requisition->created_at->format('M d, Y H:i') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge {{ $release->requisition->getStatusBadgeClass() }}">
                            {{ $release->requisition->getCurrentStage() }}
                        </span>
                    </p>
                </div>
            </div>
            
            @if($release->requisition->reason)
            <div class="mt-3 p-3 bg-light rounded">
                <h6 class="mb-2">Requisition Reason:</h6>
                <p class="mb-0">{{ $release->requisition->reason }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4 d-flex justify-content-between">
        <div>
            <a href="{{ route('stores.releases.index', $store) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Releases
            </a>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Release
            </button>
            <a href="#" class="btn btn-outline-success">
                <i class="bi bi-download"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .breadcrumb, .card-header h5 {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>
@endsection