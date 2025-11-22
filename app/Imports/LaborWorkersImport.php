<?php
// app/Imports/LaborWorkersImport.php - FIXED
namespace App\Imports;

use App\Models\LaborWorker;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LaborWorkersImport implements ToCollection, WithHeadingRow
{
    protected $projectId;
    protected $importedCount = 0;
    protected $errors = [];

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['name']) || empty($row['role']) || empty($row['payment_frequency'])) {
                    continue;
                }

                // Format phone and bank account numbers as strings
                $formattedData = [
                    'name' => $row['name'] ?? '',
                    'phone' => $this->formatPhoneNumber($row['phone'] ?? ''),
                    'email' => $row['email'] ?? null,
                    'id_number' => $this->formatString($row['id_number'] ?? ''),
                    'nssf_number' => $this->formatString($row['nssf_number'] ?? ''),
                    'bank_name' => $row['bank_name'] ?? null,
                    'bank_account' => $this->formatBankAccount($row['bank_account'] ?? ''),
                    'role' => $row['role'] ?? '',
                    'payment_frequency' => $row['payment_frequency'] ?? '',
                    'daily_rate' => $this->formatNumber($row['daily_rate'] ?? 0),
                    'monthly_rate' => $this->formatNumber($row['monthly_rate'] ?? 0),
                ];

                // Validate row data
                $validator = Validator::make($formattedData, [
                    'name' => 'required|string|max:255',
                    'phone' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:255',
                    'id_number' => 'nullable|string|max:50',
                    'nssf_number' => 'nullable|string|max:50',
                    'bank_name' => 'nullable|string|max:255',
                    'bank_account' => 'nullable|string|max:255',
                    'role' => 'required|string|max:255',
                    'payment_frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
                    'daily_rate' => 'nullable|numeric|min:0',
                    'monthly_rate' => 'nullable|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = "Row " . ($index + 2) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Validate rate fields based on payment frequency
                if ($formattedData['payment_frequency'] === 'daily' && $formattedData['daily_rate'] <= 0) {
                    $this->errors[] = "Row " . ($index + 2) . ": Daily rate is required for daily workers";
                    continue;
                }

                if ($formattedData['payment_frequency'] === 'monthly' && $formattedData['monthly_rate'] <= 0) {
                    $this->errors[] = "Row " . ($index + 2) . ": Monthly rate is required for monthly workers";
                    continue;
                }

                // Check if worker already exists
                $existingWorker = LaborWorker::where('project_id', $this->projectId)
                    ->where('name', $formattedData['name'])
                    ->where('role', $formattedData['role'])
                    ->first();

                if ($existingWorker) {
                    $this->errors[] = "Row " . ($index + 2) . ": Worker {$formattedData['name']} with role {$formattedData['role']} already exists";
                    continue;
                }

                // Create worker
                LaborWorker::create([
                    'project_id' => $this->projectId,
                    'name' => $formattedData['name'],
                    'phone' => $formattedData['phone'],
                    'email' => $formattedData['email'],
                    'id_number' => $formattedData['id_number'],
                    'nssf_number' => $formattedData['nssf_number'],
                    'bank_name' => $formattedData['bank_name'],
                    'bank_account' => $formattedData['bank_account'],
                    'role' => $formattedData['role'],
                    'payment_frequency' => $formattedData['payment_frequency'],
                    'daily_rate' => $formattedData['daily_rate'],
                    'monthly_rate' => $formattedData['monthly_rate'],
                    'start_date' => now(),
                    'status' => 'active',
                    'created_by' => auth()->id(),
                ]);

                $this->importedCount++;

            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    /**
     * Format phone number to ensure it's treated as string
     */
    private function formatPhoneNumber($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove any non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', (string)$value);
        
        // Ensure it starts with country code if needed
        if (strlen($cleaned) === 9 && !str_starts_with($cleaned, '0')) {
            $cleaned = '0' . $cleaned;
        }
        
        return $cleaned ?: null;
    }

    /**
     * Format bank account to ensure it's treated as string
     */
    private function formatBankAccount($value)
    {
        if (empty($value)) {
            return null;
        }

        // Convert to string and remove any unwanted characters
        return (string) preg_replace('/[^0-9]/', '', (string)$value) ?: null;
    }

    /**
     * Format any string field
     */
    private function formatString($value)
    {
        return empty($value) ? null : (string) $value;
    }

    /**
     * Format numeric fields
     */
    private function formatNumber($value)
    {
        if (empty($value)) {
            return 0;
        }

        // Handle both string and numeric values
        $cleaned = preg_replace('/[^0-9.]/', '', (string)$value);
        return floatval($cleaned);
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}