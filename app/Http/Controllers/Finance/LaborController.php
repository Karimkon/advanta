<?php
// app/Http/Controllers/Finance/LaborController.php - FIXED
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\LaborWorker;
use App\Models\LaborPayment;
use App\Models\Expense;
use App\Exports\LaborWorkersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\LaborWorkersImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaborController extends Controller
{
    public function index()
    {
        $workers = LaborWorker::with(['project', 'payments'])
            ->latest()
            ->paginate(20);

        return view('finance.labor.index', compact('workers'));
    }

    public function create()
    {
        $projects = Project::where('status', 'active')->get();
        return view('finance.labor.create', compact('projects'));
    }

    public function store(Request $request)
    {
        \Log::info('Labor Worker Form Data:', $request->all());
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'id_number' => 'nullable|string|max:50',
            'nssf_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
            'payment_frequency' => 'required|in:daily,weekly,monthly',
            'daily_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
        ]);

        \Log::info('Validation passed, creating labor worker...');

        try {
            // Ensure rate fields have defaults
            $validated['daily_rate'] = $validated['daily_rate'] ?? 0;
            $validated['monthly_rate'] = $validated['monthly_rate'] ?? 0;
            $validated['status'] = 'active';
            $validated['created_by'] = auth()->id();
            
            $worker = LaborWorker::create($validated);
            \Log::info('Labor worker created successfully:', ['id' => $worker->id]);

            return redirect()->route('finance.labor.index')
                ->with('success', 'Labor worker added successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating labor worker:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error adding labor worker: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function import()
    {
        $projects = Project::where('status', 'active')->get();
        return view('finance.labor.import', compact('projects'));
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new LaborWorkersImport($request->project_id);
            Excel::import($import, $request->file('import_file'));

            $importedCount = $import->getImportedCount();
            $errors = $import->getErrors();

            if (!empty($errors)) {
                return back()->with([
                    'warning' => "Imported {$importedCount} workers, but some errors occurred.",
                    'import_errors' => $errors
                ]);
            }

            return redirect()->route('finance.labor.index')
                ->with('success', "Successfully imported {$importedCount} labor workers!");

        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

      public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/labor_workers_template.xlsx');
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Create template
        $this->createTemplate($filePath);

        return response()->download($filePath, 'labor_workers_import_template.xlsx');
    }

      private function createTemplate($filePath)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'Name*', 'Phone', 'Email', 'ID Number', 'NSSF Number', 
            'Bank Name', 'Bank Account', 'Role*', 
            'Payment Frequency*', 'Daily Rate', 'Monthly Rate'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        
        // Data validation for payment frequency
        $validation = $sheet->getDataValidation('I2:I1000');
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Pick from list');
        $validation->setPrompt('Please pick a value from the drop-down list.');
        $validation->setFormula1('"daily,weekly,monthly"');

        // Style headers
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);

        // Add sample data
        $sampleData = [
            ['John Doe', '0770123456', 'john@email.com', 'CM123456789', 'NSSF123456', 'Centenary Bank', '1234567890', 'Mason', 'daily', '50000', ''],
            ['Jane Smith', '0780123456', 'jane@email.com', 'CF987654321', 'NSSF654321', 'Stanbic Bank', '0987654321', 'Carpenter', 'monthly', '', '1200000'],
            ['Mike Johnson', '0750123456', '', 'CM112233445', '', '', '', 'Helper', 'daily', '30000', ''],
        ];
        
        $sheet->fromArray($sampleData, null, 'A2');

        // Add instructions
        $sheet->setCellValue('M1', 'Import Instructions:');
        $sheet->setCellValue('M2', '1. Required fields: Name, Role, Payment Frequency');
        $sheet->setCellValue('M3', '2. Payment Frequency must be: daily, weekly, or monthly');
        $sheet->setCellValue('M4', '3. For daily/weekly workers: fill Daily Rate');
        $sheet->setCellValue('M5', '4. For monthly workers: fill Monthly Rate');
        $sheet->setCellValue('M6', '5. Do not modify the column headers');
        $sheet->setCellValue('M7', '6. Remove sample data before adding your own');

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    public function processPayment(LaborWorker $worker)
    {
        return view('finance.labor.payments.create', compact('worker'));
    }

    public function storePayment(Request $request, LaborWorker $worker)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'days_worked' => 'required|integer|min:1',
            'gross_amount' => 'required|numeric|min:0.01',
            'nssf_amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money',
            'notes' => 'nullable|string',
        ]);

        // Calculate net amount
        $netAmount = $request->gross_amount - $request->nssf_amount;

        DB::transaction(function () use ($request, $worker, $netAmount) {
            $payment = LaborPayment::create([
                'labor_worker_id' => $worker->id,
                'payment_reference' => 'LAB-PAY-' . date('Ymd') . '-' . rand(1000, 9999),
                'payment_date' => $request->payment_date,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'gross_amount' => $request->gross_amount,
                'nssf_amount' => $request->nssf_amount,
                'amount' => $netAmount, // Net amount is stored in amount field
                'net_amount' => $netAmount,
                'days_worked' => $request->days_worked,
                'description' => $request->description,
                'paid_by' => auth()->id(),
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Automatically create expense record
            Expense::create([
                'project_id' => $worker->project_id,
                'type' => 'labor',
                'description' => 'Labor Payment: ' . $request->description . ' - ' . $worker->name . ' (' . $worker->role . ')',
                'amount' => $netAmount,
                'incurred_on' => $request->payment_date,
                'recorded_by' => auth()->id(),
                'status' => 'paid',
                'notes' => $request->notes . " | Period: " . $request->period_start . " to " . $request->period_end . " | Payment Ref: " . $payment->payment_reference . " | NSSF: UGX " . number_format($request->nssf_amount, 2),
                'reference_id' => $payment->id,
                'reference_type' => LaborPayment::class,
            ]);
        });

        return redirect()->route('finance.labor.show', $worker)
            ->with('success', 'Labor payment recorded successfully!');
    }

    public function generateReceipt(LaborPayment $payment)
{
    $payment->load(['laborWorker.project', 'paidBy']);
    
    return view('finance.labor.payments.receipt', compact('payment'));
}

    public function show(LaborWorker $worker)
    {
        $worker->load(['project', 'payments' => function($query) {
            $query->orderBy('payment_date', 'desc');
        }]);

        return view('finance.labor.show', compact('worker'));
    }

    private function convertNumberToWords($number)
{
    $whole = floor($number);
    $fraction = round(($number - $whole) * 100);
    
    $words = $this->convertWholeNumberToWords($whole);
    
    if ($fraction > 0) {
        $words .= ' Shillings and ' . $this->convertWholeNumberToWords($fraction) . ' Cents';
    } else {
        $words .= ' Shillings';
    }
    
    return $words;
}

private function convertWholeNumberToWords($number)
{
    if ($number == 0) {
        return 'Zero';
    }
    
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    if ($number < 10) {
        return $ones[$number];
    } elseif ($number < 20) {
        return $teens[$number - 10];
    } elseif ($number < 100) {
        return $tens[floor($number / 10)] . ($number % 10 != 0 ? ' ' . $ones[$number % 10] : '');
    } elseif ($number < 1000) {
        return $ones[floor($number / 100)] . ' Hundred' . ($number % 100 != 0 ? ' and ' . $this->convertWholeNumberToWords($number % 100) : '');
    } elseif ($number < 1000000) {
        return $this->convertWholeNumberToWords(floor($number / 1000)) . ' Thousand' . ($number % 1000 != 0 ? ' ' . $this->convertWholeNumberToWords($number % 1000) : '');
    } elseif ($number < 1000000000) {
        return $this->convertWholeNumberToWords(floor($number / 1000000)) . ' Million' . ($number % 1000000 != 0 ? ' ' . $this->convertWholeNumberToWords($number % 1000000) : '');
    } else {
        return 'Very Large Amount';
    }
}

    /**
     * Export labor workers to Excel
     */
    public function exportExcel(Request $request)
    {
        $projectId = $request->get('project_id');
        return Excel::download(new LaborWorkersExport($projectId), 'labor_workers_' . date('Y-m-d') . '.xlsx');
    }
}