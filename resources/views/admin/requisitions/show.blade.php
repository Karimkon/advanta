@extends('admin.layouts.app')
@section('title','Requisition '.$requisition->ref)
@section('content')
<div class="container">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h4>Requisition {{ $requisition->ref }}</h4>
      <small class="text-muted">{{ $requisition->project->name }} — Requested by {{ $requisition->requester_name ?? 'N/A' }}</small>
    </div>
    <div>
      <a href="{{ route('admin.requisitions.index') }}" class="btn btn-light">Back</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6>Items</h6>
          <table class="table mb-3">
            <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
              @foreach($requisition->items as $it)
              <tr>
                <td>{{ $it->name }}</td>
                <td>{{ $it->quantity }}</td>
                <td>{{ $it->unit }}</td>
                <td>{{ number_format($it->unit_price,2) }}</td>
                <td>{{ number_format($it->total_price,2) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>

          <h6>Reason</h6>
          <p>{{ $requisition->reason }}</p>

          <h6>Attachments</h6>
          @if($requisition->attachments)
            @php $files = json_decode($requisition->attachments, true) ?: []; @endphp
            <ul>
              @foreach($files as $f)
                <li><a href="{{ asset('storage/'.$f) }}" target="_blank">{{ $f }}</a></li>
              @endforeach
            </ul>
          @else
            <p class="text-muted">No attachments</p>
          @endif
        </div>
      </div>

      <div class="mt-3">
        <h6>Approval History</h6>
        <div class="card">
          <div class="card-body">
            @foreach($requisition->approvals as $a)
              <div class="mb-2">
                <strong>{{ ucfirst($a->role) }}</strong> — <small class="text-muted">{{ $a->created_at->diffForHumans() }}</small>
                <div>{{ ucfirst($a->action) }} {!! $a->comment ? '- <em>'.$a->comment.'</em>' : '' !!}</div>
              </div>
            @endforeach
            @if($requisition->approvals->isEmpty()) <p class="text-muted">No approvals yet.</p> @endif
          </div>
        </div>
      </div>

    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6>Summary</h6>
          <p><strong>Estimated Total:</strong> {{ number_format($requisition->estimated_total,2) }}</p>
          <p><strong>Urgency:</strong> {{ ucfirst($requisition->urgency) }}</p>
          <p><strong>Status:</strong> <span class="badge bg-{{ $requisition->status=='pending' ? 'warning' : ($requisition->status=='rejected' ? 'danger' : 'success') }}">{{ $requisition->status }}</span></p>

          <hr>
          <form action="{{ route('admin.requisitions.approve', $requisition) }}" method="POST" class="d-grid gap-2 mb-2">
            @csrf
            <button class="btn btn-success" onclick="return confirm('Approve this requisition?')">Approve</button>
          </form>

          <form action="{{ route('admin.requisitions.reject', $requisition) }}" method="POST" class="d-grid gap-2 mb-2">
            @csrf
            <button class="btn btn-outline-danger" onclick="return confirm('Reject this requisition?')">Reject</button>
          </form>

          <form action="{{ route('admin.requisitions.send-to-procurement', $requisition) }}" method="POST" class="d-grid gap-2">
            @csrf
            <button class="btn btn-outline-primary" onclick="return confirm('Send to procurement?')">Send to Procurement</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection