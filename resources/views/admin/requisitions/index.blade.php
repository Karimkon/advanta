@extends('admin.layouts.app')
@section('title','Requisitions')
@section('content')
<div class="container-fluid">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between mb-3">
    <h3>Requisitions</h3>
    <div>
      <a href="{{ route('admin.requisitions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New</a>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Ref</th><th>Project</th><th>Requested By</th><th>Estimated</th><th>Urgency</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          @forelse($requisitions as $r)
            <tr>
              <td>{{ $r->ref }}</td>
              <td>{{ $r->project->name }}</td>
              <td>{{ $r->requester->name }}</td>
              <td>{{ number_format($r->estimated_total,2) }}</td>
              <td>{{ ucfirst($r->urgency) }}</td>
              <td><span class="badge bg-{{ $r->status === 'pending' ? 'warning' : ($r->status === 'rejected' ? 'danger' : 'success') }}">{{ ucfirst($r->status) }}</span></td>
              <td class="text-end">
                <a href="{{ route('admin.requisitions.show',$r) }}" class="btn btn-sm btn-outline-primary">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center py-4">No requisitions.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
