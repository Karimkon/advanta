<?php
// app/Http/Controllers/Admin/ProductCatalogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCatalog;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductCatalogTemplateExport;

class ProductCatalogController extends Controller
{
   public function index(Request $request)
{
    // Get filter parameters
    $search = $request->get('search');
    $categoryId = $request->get('category');
    $status = $request->get('status');
    
    // Start query
    $query = ProductCatalog::with(['category'])
        ->withCount(['requisitionItems', 'inventoryItems']);
    
    // Apply search filter
    if ($search) {
        $query->search($search);
    }
    
    // Apply category filter
    if ($categoryId) {
        $query->where('category_id', $categoryId);
    }
    
    // Apply status filter
    if ($status === 'active') {
        $query->where('is_active', true);
    } elseif ($status === 'inactive') {
        $query->where('is_active', false);
    }
    
    // Order and paginate
    $products = $query->latest()->paginate(20);
    
    $categories = ProductCategory::active()->orderBy('name')->get();
    
    return view('admin.product-catalog.index', compact('products', 'categories'));
}

    public function create()
    {
        $categories = ProductCategory::active()->orderBy('name')->get();
        return view('admin.product-catalog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_catalogs,name',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:product_categories,id',
            'unit' => 'required|string|max:50',
            'sku' => 'nullable|string|max:100|unique:product_catalogs,sku',
            'specifications' => 'nullable|string|max:1000',
        ]);

        try {
            ProductCatalog::create($validated);

            return redirect()->route('admin.product-catalog.index')
                ->with('success', 'Product added to catalog successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add product: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function edit(ProductCatalog $productCatalog)
    {
        $categories = ProductCategory::active()->orderBy('name')->get();
        return view('admin.product-catalog.edit', compact('productCatalog', 'categories'));
    }

    public function update(Request $request, ProductCatalog $productCatalog)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_catalogs,name,' . $productCatalog->id,
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:product_categories,id',
            'unit' => 'required|string|max:50',
            'sku' => 'nullable|string|max:100|unique:product_catalogs,sku,' . $productCatalog->id,
            'specifications' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $productCatalog->update($validated);

            return redirect()->route('admin.product-catalog.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update product: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function destroy(ProductCatalog $productCatalog)
    {
        if (!$productCatalog->canBeDeleted()) {
            return back()->with('error', 'Cannot delete product. It is being used in requisitions or inventory.');
        }

        try {
            $productCatalog->delete();

            return redirect()->route('admin.product-catalog.index')
                ->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $search = $request->get('q');
        $category = $request->get('category');

        $products = ProductCatalog::active()
            ->when($search, function($query) use ($search) {
                return $query->search($search);
            })
            ->when($category, function($query) use ($category) {
                return $query->where('category_id', $category);
            })
            ->with('category')
            ->limit(20)
            ->get(['id', 'name', 'description', 'category_id', 'unit', 'sku']);

        return response()->json($products);
    }

    // Export products to CSV/Excel for bulk upload template
    public function export()
    {
        return Excel::download(new ProductCatalogTemplateExport, 'product-catalog-template.xlsx');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240'
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('import_file');
            $extension = $file->getClientOriginalExtension();
            
            // Determine file type and use appropriate import method
            if (in_array($extension, ['xlsx', 'xls'])) {
                $result = $this->importExcelFile($file);
            } else {
                $result = $this->importCsvFile($file);
            }
            
            DB::commit();

            $message = "Bulk import completed! Imported: {$result['imported']} new products, Updated: {$result['updated']} existing products";
            if ($result['skipped'] > 0) {
                $message .= ", Skipped: {$result['skipped']} rows with errors";
            }
            if (!empty($result['errors'])) {
                session()->flash('import_errors', $result['errors']);
            }

            return redirect()->route('admin.product-catalog.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Import failed: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            return back()->with('error', 'Failed to import products: ' . $e->getMessage());
        }
    }

    private function importExcelFile($file)
    {
        $data = Excel::toArray([], $file);
        
        if (empty($data[0])) {
            throw new \Exception('Excel file is empty');
        }
        
        $rows = $data[0];
        
        \Log::info('Excel rows loaded', [
            'total_rows' => count($rows),
            'first_3_rows' => array_slice($rows, 0, 3)
        ]);
        
        // Find the actual data start row by looking for the first non-header row
        $dataStartRow = 0;
        foreach ($rows as $index => $row) {
            // Skip if row is empty
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Check if this looks like a header row (contains "name*" or "Product Name")
            $firstCell = strtolower(trim($row[0] ?? ''));
            if (strpos($firstCell, 'name') !== false || strpos($firstCell, 'product') !== false) {
                $dataStartRow = $index + 1; // Data starts after this row
                continue;
            }
            
            // If we found a row that's not a header, this is where data starts
            if ($dataStartRow > 0) {
                break;
            }
        }
        
        \Log::info('Excel data start detected', [
            'data_start_row' => $dataStartRow,
            'first_data_row' => $rows[$dataStartRow] ?? null
        ]);
        
        return $this->processImportData($rows, $dataStartRow);
    }

    private function importCsvFile($file)
    {
        $handle = fopen($file->getPathname(), 'r');
        $rows = [];
        
        // Read all rows
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);
        
        \Log::info('CSV rows loaded', [
            'total_rows' => count($rows),
            'first_row' => $rows[0] ?? []
        ]);
        
        // Auto-detect if this is a template file or simple CSV
        $skipRows = 0;
        
        // Check if first row contains template headers
        $firstRow = $rows[0] ?? [];
        $isTemplate = in_array('name*', $firstRow) || in_array('name', $firstRow);
        
        if ($isTemplate) {
            \Log::info('Detected template format - skipping 3 rows');
            $skipRows = 3; // Skip headers, instructions, sample
        } else {
            \Log::info('Detected simple CSV format - skipping 1 row (headers)');
            $skipRows = 1; // Skip only headers
        }
        
        return $this->processImportData($rows, $skipRows);
    }

    private function processImportData($rows, $skipRows = 1)
{
    $imported = 0;
    $updated = 0;
    $skipped = 0;
    $errors = [];

    foreach ($rows as $index => $data) {
        // Skip header rows
        if ($index < $skipRows) {
            \Log::info("Skipping row {$index} (header): " . implode(',', $data));
            continue;
        }
        
        // Skip empty rows
        if (empty(array_filter($data))) {
            continue;
        }

        // Map columns
        $productData = [
            'name' => trim($data[0] ?? ''),
            'description' => trim($data[1] ?? ''),
            'category_name' => trim($data[2] ?? ''),
            'unit' => trim($data[3] ?? ''),
            'sku' => trim($data[4] ?? ''),
            'specifications' => trim($data[5] ?? ''),
            'is_active' => !empty($data[6]) ? (bool)$data[6] : true,
        ];

        // Skip if this looks like a header row
        if (in_array($productData['name'], ['name*', 'name', 'Product Name (Required)']) || 
            in_array($productData['sku'], ['sku', 'SKU Code (Optional)'])) {
            \Log::info("Skipping row {$index} - detected as header");
            continue;
        }

        // Validate required fields
        if (empty($productData['name']) || empty($productData['category_name']) || empty($productData['unit'])) {
            $errors[] = "Row {$index}: Missing required fields";
            $skipped++;
            continue;
        }

        // Handle empty SKU - generate a unique one
        if (empty($productData['sku'])) {
            $productData['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        // Check for duplicate SKU
        if (!empty($productData['sku']) && ProductCatalog::where('sku', $productData['sku'])->exists()) {
            $productData['sku'] = $productData['sku'] . '-' . strtoupper(Str::random(4));
        }

        // Find or create category
        $category = ProductCategory::where('name', $productData['category_name'])->first();
        
        if (!$category) {
            $category = ProductCategory::create([
                'name' => $productData['category_name'],
                'slug' => Str::slug($productData['category_name']),
                'description' => null,
                'is_active' => true,
                'sort_order' => 0
            ]);
        }

        // Check if product exists by name
        $existingProduct = ProductCatalog::where('name', $productData['name'])->first();

        if ($existingProduct) {
            $existingProduct->update([
                'description' => $productData['description'],
                'category_id' => $category->id,
                'unit' => $productData['unit'],
                'sku' => $productData['sku'],
                'specifications' => $productData['specifications'],
                'is_active' => $productData['is_active']
            ]);
            $updated++;
        } else {
            ProductCatalog::create([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'category_id' => $category->id,
                'unit' => $productData['unit'],
                'sku' => $productData['sku'],
                'specifications' => $productData['specifications'],
                'is_active' => $productData['is_active']
            ]);
            $imported++;
        }
    }

    return [
        'imported' => $imported,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors
    ];
}

    // Export current products data
    public function exportData()
    {
        $fileName = 'product-catalog-data-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'name', 
                'description', 
                'category', 
                'unit', 
                'sku', 
                'specifications',
                'is_active',
                'created_at'
            ]);

            // Data
            $products = ProductCatalog::with('category')->get();
            
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->description,
                    $product->category->name ?? '',
                    $product->unit,
                    $product->sku,
                    $product->specifications,
                    $product->is_active ? '1' : '0',
                    $product->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}