@extends('admin.layouts.app')
@section('title','Create LPO')
@section('content')
<div class="container">
  @include('admin.partials._alerts')

  <div class="card shadow-sm">
    <div class="card-body">
      <h4>Create LPO for Procurement #{{ $procurement->id }}</h4>
      <form action="{{ route('admin.lpos.store') }}" method="POST">
        @csrf
        <input type="hidden" name="procurement_id" value="{{ $procurement->id }}">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">LPO Number</label>
            <input name="lpo_number" class="form-control" required value="{{ old('lpo_number') }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Issue Date</label>
            <input type="date" name="issued_at" class="form-control" required value="{{ old('issued_at', now()->format('Y-m-d')) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Subtotal</label>
            <input type="number" step="0.01" name="subtotal" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tax</label>
            <input type="number" step="0.01" name="tax" class="form-control" value="0">
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control"></textarea>
          </div>

          <div class="col-12 text-end">
            <a href="{{ route('admin.lpos.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary">Issue LPO</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
