<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Requisition;
use Illuminate\Http\Request;

class EngineerDashboardController extends Controller
{
    public function index()
    {
        $engineer = auth()->user();
        
        // Get projects assigned to this engineer
        $projects = Project::whereHas('users', function($query) use ($engineer) {
            $query->where('user_id', $engineer->id);
        })->get();

        // Get requisitions created by this engineer
        $requisitions = Requisition::where('requested_by', $engineer->id)
            ->with('project')
            ->latest()
            ->take(5)
            ->get();

        return view('engineer.dashboard', compact('projects', 'requisitions'));
    }
}