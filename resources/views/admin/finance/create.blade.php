@extends('admin.layouts.app')
@section('title','Record Payment')
@section('content')
<div class="container">
  @include('admin.partials._alerts')

  <div class="card shadow-sm">
    <div class="card-body">
      <h4>Record Payment</h4>
      <form action="{{ route('admin.finance.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">LPO</label>
            <select name="lpo_id" class="form-select select2" required>
              <option value="">Select LPO</option>
              @foreach(\App\Models\Lpo::latest()->get() as $l)
                <option value="{{ $l->id }}">{{ $l->lpo_number }} — {{ $l->supplier->name }} ({{ number_format($l->total,2) }})</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Amount Paid</label>
            <input type="number" step="0.01" name="amount_paid" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Payment Date</label>
            <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Reference</label>
            <input name="reference_number" class="form-control" required>
          </div>

          <div class="col-12 text-end">
            <a href="{{ route('admin.finance.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary">Record Payment</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>
@endsection
