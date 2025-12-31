@extends('admin.layouts.app')
@section('title', 'Fix LPO')
@section('content')
<div class="container-fluid">
    @include('admin.partials._alerts')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Fix LPO: {{ $lpo->lpo_number }}</h2>
            <p class="text-muted mb-0">Correct LPO issues - pricing, status, etc.</p>
        </div>
        <a href="{{ route('admin.lpos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to LPOs
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- LPO Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">LPO Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>LPO Number:</strong> {{ $lpo->lpo_number }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $lpo->status === 'draft' ? 'warning' : ($lpo->status === 'issued' ? 'success' : 'secondary') }}">
                            {{ ucfirst($lpo->status) }}
                        </span>
                    </p>
                    <p><strong>Total:</strong> UGX {{ number_format($lpo->total, 2) }}</p>
                    <p><strong>Supplier:</strong> {{ $lpo->supplier->name ?? 'N/A' }}</p>
                    <p><strong>Created:</strong> {{ $lpo->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Requisition Info -->
            @if($lpo->requisition)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Requisition Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Reference:</strong> {{ $lpo->requisition->ref }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $lpo->requisition->status === 'rejected' ? 'danger' : ($lpo->requisition->status === 'procurement' ? 'info' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $lpo->requisition->status)) }}
                        </span>
                    </p>
                    <p><strong>Project:</strong> {{ $lpo->requisition->project->name ?? 'N/A' }}</p>
                    <p><strong>Requested By:</strong> {{ $lpo->requisition->requester->name ?? 'N/A' }}</p>
                </div>
            </div>
            @endif

            <!-- Quick Fix Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">Quick Fix Actions</h5>
                </div>
                <div class="card-body">
                    @if($lpo->requisition && $lpo->requisition->status === 'rejected')
                    <form action="{{ route('admin.lpos.fix-status', $lpo) }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="requisition_status" value="procurement">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Set Requisition to "Procurement"
                        </button>
                        <small class="text-muted">This will allow CEO to see the LPO for approval</small>
                    </form>
                    @endif

                    @if($lpo->status !== 'draft')
                    <form action="{{ route('admin.lpos.fix-status', $lpo) }}" method="POST">
                        @csrf
                        <input type="hidden" name="lpo_status" value="draft">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset LPO to "Draft"
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Update Item Prices -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Update Item Prices</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.lpos.update-prices', $lpo) }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Description</th>
                                        <th width="120">Quantity</th>
                                        <th width="150">Unit Price (UGX)</th>
                                        <th width="150">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lpo->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>
                                            <input type="number"
                                                   name="items[{{ $item->id }}][quantity]"
                                                   value="{{ $item->quantity }}"
                                                   step="0.001"
                                                   min="0.001"
                                                   class="form-control form-control-sm item-qty"
                                                   data-id="{{ $item->id }}"
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   name="items[{{ $item->id }}][unit_price]"
                                                   value="{{ $item->unit_price }}"
                                                   step="0.01"
                                                   min="0"
                                                   class="form-control form-control-sm item-price"
                                                   data-id="{{ $item->id }}"
                                                   required>
                                        </td>
                                        <td>
                                            <span class="item-total" id="total-{{ $item->id }}">
                                                UGX {{ number_format($item->quantity * $item->unit_price, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <th colspan="3" class="text-end">Grand Total:</th>
                                        <th id="grand-total">UGX {{ number_format($lpo->items->sum(fn($i) => $i->quantity * $i->unit_price), 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Prices
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('.item-price').forEach(priceInput => {
            const id = priceInput.dataset.id;
            const qtyInput = document.querySelector(`.item-qty[data-id="${id}"]`);
            const qty = parseFloat(qtyInput?.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const total = qty * price;
            grandTotal += total;

            document.getElementById(`total-${id}`).textContent =
                'UGX ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        });

        document.getElementById('grand-total').textContent =
            'UGX ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    document.querySelectorAll('.item-qty, .item-price').forEach(input => {
        input.addEventListener('input', updateTotals);
    });
});
</script>
@endsection
