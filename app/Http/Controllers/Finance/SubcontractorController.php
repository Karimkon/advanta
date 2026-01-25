<?php
// app/Http/Controllers/Finance/SubcontractorController.php - FIXED
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Subcontractor;
use App\Models\ProjectSubcontractor;
use App\Models\SubcontractorPayment;
use App\Exports\SubcontractorPaymentsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class SubcontractorController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcontractor::with(['projectSubcontractors.project', 'payments'])
            ->withCount('projectSubcontractors');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
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

        return view('finance.subcontractors.index', compact('subcontractors', 'specializations'));
    }

    public function create()
    {
        $projects = Project::where('status', 'active')->get();
        return view('finance.subcontractors.create', compact('projects'));
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
            'projects' => 'required|array',
            'projects.*.contract_amount' => 'required|numeric|min:0',
            'projects.*.work_description' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only([
                'name', 'contact_person', 'phone', 'email',
                'specialization', 'address', 'tax_number'
            ]);

            // Hash password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $subcontractor = Subcontractor::create($data);

            foreach ($request->projects as $projectData) {
                ProjectSubcontractor::create([
                    'project_id' => $projectData['project_id'],
                    'subcontractor_id' => $subcontractor->id,
                    'contract_number' => 'CNT-' . date('Ymd') . '-' . rand(1000, 9999),
                    'work_description' => $projectData['work_description'],
                    'contract_amount' => $projectData['contract_amount'],
                    'start_date' => $projectData['start_date'],
                    'terms' => $projectData['terms'] ?? null,
                ]);
            }
        });

        return redirect()->route('finance.subcontractors.index')
            ->with('success', 'Subcontractor added successfully!');
    }

    public function show(Subcontractor $subcontractor)
    {
        // FIXED: Use correct relationship loading
        $subcontractor->load([
            'projectSubcontractors.project', 
            'payments.projectSubcontractor.project'
        ]);
        
        return view('finance.subcontractors.show', compact('subcontractor'));
    }

    public function ledger(ProjectSubcontractor $projectSubcontractor)
    {
        $projectSubcontractor->load([
            'payments' => function($query) {
                $query->orderBy('payment_date', 'asc');
            }, 
            'project', 
            'subcontractor'
        ]);

        $ledger = [];
        $runningBalance = $projectSubcontractor->contract_amount;

        foreach ($projectSubcontractor->payments as $payment) {
            $runningBalance -= $payment->amount;
            $ledger[] = [
                'date' => $payment->payment_date,
                'description' => $payment->description,
                'debit' => 0,
                'credit' => $payment->amount,
                'balance' => $runningBalance,
                'type' => 'payment',
                'reference' => $payment->payment_reference
            ];
        }

        // Add initial contract as first entry
        array_unshift($ledger, [
            'date' => $projectSubcontractor->start_date,
            'description' => 'Contract Agreement - ' . $projectSubcontractor->work_description,
            'debit' => $projectSubcontractor->contract_amount,
            'credit' => 0,
            'balance' => $projectSubcontractor->contract_amount,
            'type' => 'contract',
            'reference' => $projectSubcontractor->contract_number
        ]);

        return view('finance.subcontractors.ledger', compact('projectSubcontractor', 'ledger'));
    }

    /**
     * Export subcontractor payments to Excel
     */
    public function exportExcel()
    {
        return Excel::download(new SubcontractorPaymentsExport, 'subcontractor_payments_' . date('Y-m-d') . '.xlsx');
    }
}