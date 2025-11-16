@extends('admin.layouts.app')
@section('title','LPO '.$lpo->lpo_number)
@section('content')
<div class="container">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between mb-3">
    <h4>LPO {{ $lpo->lpo_number }}</h4>
    <div>
      <a href="{{ route('admin.lpos.index') }}" class="btn btn-light">Back</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <p><strong>Supplier:</strong> {{ $lpo->supplier->name }}</p>
      <p><strong>Project:</strong> {{ optional($lpo->requisition->project)->name }}</p>
      <p><strong>Issued:</strong> {{ $lpo->issued_at?->format('d M Y') }}</p>

      <hr>
      <h6>Items</h6>
      <table class="table">
        <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>
          @foreach($lpo->items as $it)
            <tr>
              <td>{{ $it->description }}</td>
              <td>{{ $it->quantity }}</td>
              <td>{{ $it->unit }}</td>
              <td>{{ number_format($it->unit_price,2) }}</td>
              <td>{{ number_format($it->total_price,2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="text-end">
        <p><strong>Subtotal:</strong> {{ number_format($lpo->subtotal,2) }}</p>
        <p><strong>Tax:</strong> {{ number_format($lpo->tax,2) }}</p>
        <p><strong>Total:</strong> <strong>{{ number_format($lpo->total,2) }}</strong></p>
      </div>
    </div>
  </div>
</div>
@endsection
