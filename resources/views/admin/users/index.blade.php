@extends('admin.layouts.app')
@section('title','Users')
@section('content')
<div class="container-fluid">
  @include('admin.partials._alerts')

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1">Users & Roles Management</h2>
      <p class="text-muted mb-0">Manage system users and their roles</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Create User
    </a>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h5 class="mb-0">All Users ({{ $users->total() }})</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $user)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                      <i class="bi bi-person-fill text-primary"></i>
                    </div>
                    <div>
                      <strong>{{ $user->name }}</strong>
                      @if($user->id === auth()->id())
                        <span class="badge bg-info ms-1">You</span>
                      @endif
                    </div>
                  </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone ?? 'N/A' }}</td>
                <td>
                  <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                  @if($user->role === 'stores' && $user->store)
                    <br><small class="text-muted">
                      <i class="bi bi-shop me-1"></i>{{ $user->store->name }}
                    </small>
                  @elseif($user->role === 'stores' && !$user->store)
                    <br><small class="text-warning">
                      <i class="bi bi-exclamation-triangle me-1"></i>No store assigned
                    </small>
                  @endif
                </td>
                <td>
                  <span class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>Active
                  </span>
                </td>
                <td>
                  <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="btn btn-outline-primary" 
                       title="Edit User">
                      <i class="bi bi-pencil"></i>
                    </a>
                    @if($user->id !== auth()->id())
                      <button type="button" 
                              class="btn btn-outline-danger" 
                              title="Delete User"
                              onclick="confirmDelete({{ $user->id }})">
                        <i class="bi bi-trash"></i>
                      </button>
                      <form id="delete-form-{{ $user->id }}" 
                            action="{{ route('admin.users.destroy', $user) }}" 
                            method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">
                  <div class="text-muted">
                    <i class="bi bi-people display-4 d-block mb-2"></i>
                    No users found.
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    
    @if($users->hasPages())
      <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if($users->onFirstPage())
              <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
              <li class="page-item"><a class="page-link" href="{{ $users->previousPageUrl() }}">&laquo;</a></li>
            @endif

            {{-- Page Numbers --}}
            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
              @if($page == $users->currentPage())
                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
              @else
                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
              @endif
            @endforeach

            {{-- Next Page Link --}}
            @if($users->hasMorePages())
              <li class="page-item"><a class="page-link" href="{{ $users->nextPageUrl() }}">&raquo;</a></li>
            @else
              <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
          </ul>
        </nav>
      </div>
    @endif
  </div>

  <!-- Quick Stats -->
  <div class="row mt-4">
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h4>{{ $users->total() }}</h4>
              <p class="mb-0">Total Users</p>
            </div>
            <i class="bi bi-people fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h4>{{ $users->where('role', 'admin')->count() }}</h4>
              <p class="mb-0">Admins</p>
            </div>
            <i class="bi bi-shield-check fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h4>{{ $users->whereIn('role', ['project_manager', 'site_manager'])->count() }}</h4>
              <p class="mb-0">Managers</p>
            </div>
            <i class="bi bi-briefcase fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning text-white">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h4>{{ $users->where('role', 'supplier')->count() }}</h4>
              <p class="mb-0">Suppliers</p>
            </div>
            <i class="bi bi-truck fs-1 opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(userId) {
  if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
    document.getElementById('delete-form-' + userId).submit();
  }
}
</script>

<style>
.avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
@endpush