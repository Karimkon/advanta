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
                                    <th>Requested Amount:</th>
                                    <td><strong>UGX {{ number_format($requisition->estimated_total, 2) }}</strong></td>
                                </tr>
                                @if($requisition->lpo && $requisition->lpo->receivedItems->count() > 0)
                                @php
                                    $actualAmount = 0;
                                    foreach($requisition->lpo->receivedItems as $receivedItem) {
                                        if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0) {
                                            $actualAmount += $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <th>Actual Amount:</th>
                                    <td>
                                        <strong class="text-success">UGX {{ number_format($actualAmount, 2) }}</strong>
                                        @if($actualAmount != $requisition->estimated_total)
                                            <br><small class="text-muted">Based on actual delivery</small>
                                        @endif
                                    </td>
                                </tr>
                                @endif
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
                                    <th>Requested Qty</th>
                                    <th>Received Qty</th>
                                    <th>Unit</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisition->items as $item)
                                    @php
                                        $receivedQty = 0;
                                        $lpoItem = $requisition->lpo ? $requisition->lpo->items->where('description', $item->name)->first() : null;
                                        if ($lpoItem && $requisition->lpo->receivedItems) {
                                            $receivedItem = $requisition->lpo->receivedItems->where('lpo_item_id', $lpoItem->id)->first();
                                            $receivedQty = $receivedItem ? $receivedItem->quantity_received : 0;
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $item->name }}
                                            @if($item->from_store)
                                                <span class="badge bg-success ms-1">From Store</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            @if($receivedQty > 0)
                                                <span class="{{ $receivedQty == $item->quantity ? 'text-success' : 'text-warning' }}">
                                                    {{ $receivedQty }}
                                                    @if($receivedQty != $item->quantity)
                                                        <br><small class="text-muted">({{ number_format(($receivedQty/$item->quantity)*100, 1) }}%)</small>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->unit }}</td>
                                        <td>UGX {{ number_format($item->unit_price, 2) }}</td>
                                        <td>
                                            <strong>UGX {{ number_format($item->total_price, 2) }}</strong>
                                            @if($receivedQty > 0 && $receivedQty != $item->quantity)
                                                <br><small class="text-success">Actual: UGX {{ number_format($receivedQty * $item->unit_price, 2) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                    <td colspan="2"><strong>UGX {{ number_format($requisition->estimated_total, 2) }}</strong></td>
                                </tr>
                                @if($requisition->lpo && $requisition->lpo->receivedItems->count() > 0)
                                @php
                                    $actualTotal = 0;
                                    foreach($requisition->lpo->receivedItems as $receivedItem) {
                                        if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0) {
                                            $actualTotal += $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
                                        }
                                    }
                                @endphp
                                @if($actualTotal != $requisition->estimated_total)
                                <tr class="table-success">
                                    <td colspan="5" class="text-end"><strong>Actual Total:</strong></td>
                                    <td colspan="2"><strong class="text-success">UGX {{ number_format($actualTotal, 2) }}</strong></td>
                                </tr>
                                @endif
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rest of the view remains the same -->
    </div>
</div>
@endsection