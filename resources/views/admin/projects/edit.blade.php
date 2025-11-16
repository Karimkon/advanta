@extends('admin.layouts.app')
@section('title','Edit Project')
@section('content')
<div class="container">
  @include('admin.partials._alerts')

  <div class="card shadow-sm">
    <div class="card-body">
      <h4>Edit Project</h4>
      <form action="{{ route('admin.projects.update', $project) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required value="{{ old('name',$project->name) }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Manager</label>
            <select name="manager_id" class="form-select select2" required>
              <option value="">Select manager</option>
              @foreach($managers as $m)
                <option value="{{ $m->id }}" @selected(old('manager_id',$project->manager_id) == $m->id)>{{ $m->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" required value="{{ old('start_date',$project->start_date?->format('Y-m-d')) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date',$project->end_date?->format('Y-m-d')) }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Budget</label>
            <input type="number" step="0.01" name="budget" class="form-control" required value="{{ old('budget',$project->budget) }}">
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description',$project->description) }}</textarea>
          </div>

          <div class="col-12 text-end">
            <a href="{{ route('admin.projects.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn btn-primary">Update Project</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
