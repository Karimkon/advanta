<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Project;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['project', 'recordedBy'])
            ->latest()
            ->paginate(20);

        $projects = Project::all();
        $categories = ['Materials', 'Labor', 'Equipment', 'Transport', 'Utilities', 'Other'];

        return view('finance.expenses.index', compact('expenses', 'projects', 'categories'));
    }

    public function create()
    {
        $projects = Project::all();
        $categories = ['Materials', 'Labor', 'Equipment', 'Transport', 'Utilities', 'Other'];

        return view('finance.expenses.create', compact('projects', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'incurred_on' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Expense::create([
            'project_id' => $request->project_id,
            'type' => $request->type,
            'description' => $request->description,
            'amount' => $request->amount,
            'incurred_on' => $request->incurred_on,
            'recorded_by' => auth()->id(),
            'status' => 'paid', // or 'unpaid' based on your business logic
            'notes' => $request->notes,
        ]);

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Expense recorded successfully!');
    }

    public function show($id)
    {
        $expense = Expense::with(['project', 'recordedBy'])->findOrFail($id);
        return view('finance.expenses.show', compact('expense'));
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $projects = Project::all();
        $categories = ['Materials', 'Labor', 'Equipment', 'Transport', 'Utilities', 'Other'];

        return view('finance.expenses.edit', compact('expense', 'projects', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type' => 'required|string',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'incurred_on' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update([
            'project_id' => $request->project_id,
            'type' => $request->type,
            'description' => $request->description,
            'amount' => $request->amount,
            'incurred_on' => $request->incurred_on,
            'notes' => $request->notes,
        ]);

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('finance.expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    public function reports()
    {
        $expenses = Expense::with('project')
            ->whereBetween('incurred_on', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();

        $categoryTotals = $expenses->groupBy('type')->map->sum('amount');
        $projectTotals = $expenses->groupBy('project.name')->map->sum('amount');

        return view('finance.expenses.reports', compact('expenses', 'categoryTotals', 'projectTotals'));
    }

    public function export()
    {
        $expenses = Expense::with('project')
            ->whereBetween('incurred_on', [now()->subMonth(), now()])
            ->get();

        return response()->streamDownload(function () use ($expenses) {
            echo "Date,Project,Type,Description,Amount,Status\n";
            foreach ($expenses as $expense) {
                echo "{$expense->incurred_on},{$expense->project->name},{$expense->type},{$expense->description},{$expense->amount},{$expense->status}\n";
            }
        }, 'expenses_export_' . date('Y-m-d') . '.csv');
    }
}