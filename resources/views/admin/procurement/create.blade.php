@extends('admin.layouts.app')
@section('title','Procurement Review')
@section('content')
<div class="container">
  @include('admin.partials._alerts')

  <div class="card shadow-sm">
    <div class="card-body">
      <h4>Procurement for Requisition {{ $requisition->ref }}</h4>

      <form action="{{ route('admin.procurement.store') }}" method="POST">
        @csrf
        <input type="hidden" name="requisition_id" value="{{ $requisition->id }}">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Supplier Name</label>
            <input name="supplier_name" class="form-control" required value="{{ old('supplier_name') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Supplier Contact</label>
            <input name="supplier_contact" class="form-control" value="{{ old('supplier_contact') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Evaluated Cost</label>
            <input type="number" step="0.01" name="evaluated_cost" class="form-control" required>
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
          </div>

          <div class="col-12 text-end">
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary">Save Procurement</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
