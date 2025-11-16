@extends('admin.layouts.app')
@section('title','Procurement')
@section('content')
<div class="container-fluid">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between mb-3">
    <h3>Procurement Reviews</h3>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table mb-0">
        <thead><tr><th>Requisition</th><th>Project</th><th>Supplier</th><th>Evaluated Cost</th><th>Added</th><th></th></tr></thead>
        <tbody>
          @forelse($items as $proc)
            <tr>
              <td><a href="{{ route('admin.requisitions.show', $proc->requisition) }}">{{ $proc->requisition->ref }}</a></td>
              <td>{{ $proc->requisition->project->name }}</td>
              <td>{{ $proc->supplier_name }}</td>
              <td>{{ number_format($proc->evaluated_cost,2) }}</td>
              <td>{{ $proc->created_at->diffForHumans() }}</td>
              <td class="text-end">
                <a href="{{ route('admin.lpos.create', $proc) }}" class="btn btn-sm btn-primary">Create LPO</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center py-4">No procurement entries yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
    