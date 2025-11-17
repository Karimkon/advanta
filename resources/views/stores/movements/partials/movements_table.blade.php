<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Date & Time</th>
                <th>Item Details</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Value</th>
                <th>Balance After</th>
                <th>User</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>
                        <div class="d-flex flex-column">
                            <small class="text-muted">{{ $movement->created_at->format('M d, Y') }}</small>
                            <small class="text-muted">{{ $movement->created_at->format('H:i:s') }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong>{{ $movement->item->name ?? 'Unknown Item' }}</strong>
                            <small class="text-muted">SKU: {{ $movement->item->sku ?? 'N/A' }}</small>
                            <small class="text-muted">Category: {{ $movement->item->category ?? 'N/A' }}</small>
                        </div>
                    </td>
                    <td>
                        @if($movement->type === 'in')
                            <span class="badge bg-success">
                                <i class="bi bi-arrow-down-circle"></i> IN
                            </span>
                        @elseif($movement->type === 'out')
                            <span class="badge bg-warning">
                                <i class="bi bi-arrow-up-circle"></i> OUT
                            </span>
                        @else
                            <span class="badge bg-info">
                                <i class="bi bi-arrow-left-right"></i> ADJUST
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong class="{{ $movement->type === 'in' ? 'text-success' : ($movement->type === 'out' ? 'text-warning' : 'text-info') }}">
                                {{ $movement->type === 'in' ? '+' : ($movement->type === 'out' ? '-' : '±') }}{{ $movement->quantity }}
                            </strong>
                            <small class="text-muted">{{ $movement->item->unit ?? '' }}</small>
                        </div>
                    </td>
                    <td>
                        <small>UGX {{ number_format($movement->unit_price, 2) }}</small>
                    </td>
                    <td>
                        <strong class="{{ $movement->type === 'in' ? 'text-success' : ($movement->type === 'out' ? 'text-warning' : 'text-info') }}">
                            UGX {{ number_format($movement->quantity * $movement->unit_price, 2) }}
                        </strong>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong>{{ $movement->balance_after }}</strong>
                            <small class="text-muted">{{ $movement->item->unit ?? '' }}</small>
                        </div>
                    </td>
                    <td>
                        <small>{{ $movement->user->name ?? 'System' }}</small>
                    </td>
                    <td>
                        @if($movement->notes)
                            <small class="text-muted" data-bs-toggle="tooltip" title="{{ $movement->notes }}">
                                <i class="bi bi-chat-left-text"></i> {{ Str::limit($movement->notes, 30) }}
                            </small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            No stock movements found for the selected filters.
                            <p class="mt-2 small">Try adjusting your filter criteria.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($movements->hasPages())
    <div class="card-footer bg-white">
        {{ $movements->links() }}
    </div>
@endif

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>
@endpush