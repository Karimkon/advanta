@extends('admin.layouts.app')
@section('title','LPOs')
@section('content')
<div class="container-fluid">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between mb-3">
    <h3>Local Purchase Orders (LPOs)</h3>
    <div></div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table mb-0">
        <thead><tr><th>LPO#</th><th>Supplier</th><th>Project</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @forelse($lpos as $l)
            <tr>
              <td>{{ $l->lpo_number }}</td>
              <td>{{ $l->supplier->name }}</td>
              <td>{{ optional($l->requisition->project)->name }}</td>
              <td>{{ number_format($l->total,2) }}</td>
              <td>{{ ucfirst($l->status) }}</td>
              <td class="text-end">
                <a href="{{ route('admin.lpos.show',$l) }}" class="btn btn-sm btn-outline-primary">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center py-4">No LPOs issued yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
