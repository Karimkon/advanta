<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminClientController extends Controller
{
    /**
     * Display list of all clients
     */
    public function index()
    {
        $clients = Client::withCount('projects')
            ->latest()
            ->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Show form to create a new client
     */
    public function create()
    {
        $projects = Project::where('status', 'active')->get();
        return view('admin.clients.create', compact('projects'));
    }

    /**
     * Store a new client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:projects,id',
        ]);

        $client = Client::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        // Attach projects if selected
        if (!empty($validated['projects'])) {
            $client->projects()->attach($validated['projects']);
        }

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully! Login credentials have been set.');
    }

    /**
     * Show client details
     */
    public function show(Client $client)
    {
        $client->load(['projects.milestones']);
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Show form to edit client
     */
    public function edit(Client $client)
    {
        $projects = Project::where('status', 'active')->get();
        $assignedProjectIds = $client->projects->pluck('id')->toArray();

        return view('admin.clients.edit', compact('client', 'projects', 'assignedProjectIds'));
    }

    /**
     * Update client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('clients')->ignore($client->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:projects,id',
        ]);

        $client->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $client->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync projects
        $client->projects()->sync($validated['projects'] ?? []);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Delete client
     */
    public function destroy(Client $client)
    {
        $client->projects()->detach();
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully!');
    }

    /**
     * Manage project assignments for a client
     */
    public function projects(Client $client)
    {
        $allProjects = Project::where('status', 'active')->get();
        $assignedProjectIds = $client->projects->pluck('id')->toArray();

        return view('admin.clients.projects', compact('client', 'allProjects', 'assignedProjectIds'));
    }

    /**
     * Update project assignments
     */
    public function updateProjects(Request $request, Client $client)
    {
        $validated = $request->validate([
            'projects' => 'nullable|array',
            'projects.*' => 'exists:projects,id',
        ]);

        $client->projects()->sync($validated['projects'] ?? []);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Project assignments updated successfully!');
    }
}
