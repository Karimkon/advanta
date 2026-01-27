<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\ProjectSubcontractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSubcontractorController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcontractor::with(['projectSubcontractors.project', 'projectSubcontractors.payments'])
            ->withCount('projectSubcontractors');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by specialization
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        $subcontractors = $query->latest()->paginate(20)->withQueryString();

        // Get unique specializations for filter dropdown
        $specializations = Subcontractor::distinct()->pluck('specialization')->filter();

        // Statistics
        $totalSubcontractors = Subcontractor::count();
        $activeSubcontractors = Subcontractor::where('status', 'active')->count();
        $totalContractValue = ProjectSubcontractor::sum('contract_amount');
        $totalPaid = DB::table('subcontractor_payments')->sum('amount');

        return view('admin.subcontractors.index', compact(
            'subcontractors',
            'specializations',
            'totalSubcontractors',
            'activeSubcontractors',
            'totalContractValue',
            'totalPaid'
        ));
    }

    public function create()
    {
        $projects = Project::where('status', 'active')->get();
        return view('admin.subcontractors.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'specialization' => 'required|string|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only([
                'name', 'contact_person', 'phone', 'email',
                'specialization', 'address', 'tax_number', 'status'
            ]);

            // Hash password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $subcontractor = Subcontractor::create($data);

            // Create project contracts if provided
            if ($request->has('projects')) {
                foreach ($request->projects as $projectData) {
                    if (!empty($projectData['project_id']) && !empty($projectData['contract_amount'])) {
                        ProjectSubcontractor::create([
                            'project_id' => $projectData['project_id'],
                            'subcontractor_id' => $subcontractor->id,
                            'contract_number' => 'CNT-' . date('Ymd') . '-' . rand(1000, 9999),
                            'work_description' => $projectData['work_description'] ?? '',
                            'contract_amount' => $projectData['contract_amount'],
                            'start_date' => $projectData['start_date'] ?? now(),
                            'end_date' => $projectData['end_date'] ?? null,
                            'terms' => $projectData['terms'] ?? null,
                            'status' => 'active',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.subcontractors.index')
            ->with('success', 'Subcontractor created successfully!');
    }

    public function show(Subcontractor $subcontractor)
    {
        $subcontractor->load([
            'projectSubcontractors.project',
            'projectSubcontractors.payments.paidBy'
        ]);

        // Get projects for the "Add Contract" modal
        $projects = Project::where('status', 'active')->get();

        return view('admin.subcontractors.show', compact('subcontractor', 'projects'));
    }

    public function edit(Subcontractor $subcontractor)
    {
        $subcontractor->load(['projectSubcontractors.project']);
        $projects = Project::where('status', 'active')->get();

        return view('admin.subcontractors.edit', compact('subcontractor', 'projects'));
    }

    public function update(Request $request, Subcontractor $subcontractor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'specialization' => 'required|string|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'name', 'contact_person', 'phone', 'email',
            'specialization', 'address', 'tax_number', 'status'
        ]);

        // Hash password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $subcontractor->update($data);

        return redirect()->route('admin.subcontractors.index')
            ->with('success', 'Subcontractor updated successfully!');
    }

    public function destroy(Subcontractor $subcontractor)
    {
        // Check if subcontractor has any contracts
        if ($subcontractor->projectSubcontractors()->count() > 0) {
            return redirect()->route('admin.subcontractors.index')
                ->with('error', 'Cannot delete subcontractor with existing contracts. Please remove contracts first or deactivate the subcontractor.');
        }

        $subcontractor->delete();

        return redirect()->route('admin.subcontractors.index')
            ->with('success', 'Subcontractor deleted successfully!');
    }

    // Contract Management
    public function editContract(ProjectSubcontractor $projectSubcontractor)
    {
        $projectSubcontractor->load(['project', 'subcontractor', 'payments']);
        $contract = $projectSubcontractor;
        return view('admin.subcontractors.edit-contract', compact('contract'));
    }

    public function updateContract(Request $request, ProjectSubcontractor $projectSubcontractor)
    {
        $request->validate([
            'work_description' => 'required|string',
            'contract_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'terms' => 'nullable|string',
            'status' => 'required|in:active,completed,terminated',
        ]);

        $projectSubcontractor->update($request->only([
            'work_description', 'contract_amount', 'start_date',
            'end_date', 'terms', 'status'
        ]));

        return redirect()->route('admin.subcontractors.show', $projectSubcontractor->subcontractor_id)
            ->with('success', 'Contract updated successfully!');
    }

    public function destroyContract(ProjectSubcontractor $projectSubcontractor)
    {
        // Check if contract has payments
        if ($projectSubcontractor->payments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete contract with existing payments.');
        }

        $subcontractorId = $projectSubcontractor->subcontractor_id;
        $projectSubcontractor->delete();

        return redirect()->route('admin.subcontractors.show', $subcontractorId)
            ->with('success', 'Contract deleted successfully!');
    }

    public function addContract(Request $request, Subcontractor $subcontractor)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'work_description' => 'required|string',
            'contract_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'terms' => 'nullable|string',
        ]);

        // Check for duplicate
        $exists = ProjectSubcontractor::where('project_id', $request->project_id)
            ->where('subcontractor_id', $subcontractor->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'A contract already exists for this subcontractor on this project.');
        }

        ProjectSubcontractor::create([
            'project_id' => $request->project_id,
            'subcontractor_id' => $subcontractor->id,
            'contract_number' => 'CNT-' . date('Ymd') . '-' . rand(1000, 9999),
            'work_description' => $request->work_description,
            'contract_amount' => $request->contract_amount,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'terms' => $request->terms,
            'status' => 'active',
        ]);

        return redirect()->route('admin.subcontractors.show', $subcontractor)
            ->with('success', 'Contract added successfully!');
    }
}
