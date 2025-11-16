@extends('admin.layouts.app')
@section('title','Finance')
@section('content')
<div class="container-fluid">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between mb-3">
    <h3>Payments & Expenses</h3>
    <div>
      <a href="{{ route('admin.finance.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Payment</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table mb-0">
        <thead><tr><th>Ref</th><th>Type</th><th>Project</th><th>Amount</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @forelse($records as $rec)
            <tr>
              <td>{{ $rec->id }}</td>
              <td>{{ $rec->type ?? 'payment' }}</td>
              <td>{{ optional($rec->lpo->requisition->project)->name }}</td>
              <td>{{ number_format($rec->amount,2) }}</td>
              <td>{{ ucfirst($rec->status) }}</td>
              <td class="text-end">
                {{-- view button --}}
                <a href="#" class="btn btn-sm btn-outline-primary">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center py-4">No finance records yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
