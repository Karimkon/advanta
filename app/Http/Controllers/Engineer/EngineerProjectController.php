<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngineerProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get projects assigned to this engineer
        $projects = $user->projects()->with(['users' => function($query) {
            $query->where('role', 'project_manager');
        }])->latest()->get();

        return view('engineer.projects.index', compact('projects'));
    }
}