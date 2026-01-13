@extends('exports.pdf.layout')

@section('content')
<div class="summary-box">
    <h3>Summary</h3>
    <table style="width: 50%; border: none;">
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Projects:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $projects->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Active Projects:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>{{ $projects->where('status', 'active')->count() }}</strong></td>
        </tr>
        <tr style="border: none;">
            <td style="border: none; padding: 3px 0;"><span class="summary-label">Total Budget:</span></td>
            <td style="border: none; padding: 3px 0;"><strong>UGX {{ number_format($projects->sum('budget'), 2) }}</strong></td>
        </tr>
    </table>
</div>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Project Name</th>
            <th>Location</th>
            <th>Manager</th>
            <th class="text-right">Budget (UGX)</th>
            <th class="text-center">Status</th>
            <th>Start Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projects as $project)
        @php
            $projectManager = $project->users->where('role', 'project_manager')->first();
        @endphp
        <tr>
            <td>{{ $project->code }}</td>
            <td>{{ $project->name }}</td>
            <td>{{ $project->location }}</td>
            <td>{{ $projectManager ? $projectManager->name : 'Not Assigned' }}</td>
            <td class="amount">{{ number_format($project->budget, 2) }}</td>
            <td class="text-center">
                <span class="badge badge-{{ $project->status === 'active' ? 'success' : ($project->status === 'completed' ? 'info' : 'secondary') }}">
                    {{ ucfirst($project->status) }}
                </span>
            </td>
            <td>{{ $project->start_date ? date('M d, Y', strtotime($project->start_date)) : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="4"><strong>TOTAL</strong></td>
            <td class="amount">{{ number_format($projects->sum('budget'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>
@endsection
