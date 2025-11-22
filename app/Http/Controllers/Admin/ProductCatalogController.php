<?php
// app/Http/Controllers/Admin/ProductCatalogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCatalogController extends Controller
{
    public function index()
    {
        $products = ProductCatalog::withCount(['requisitionItems', 'inventoryItems'])
            ->latest()
            ->paginate(20);

        $categories = ProductCatalog::distinct()->pluck('category');

        return view('admin.product-catalog.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = ProductCatalog::distinct()->pluck('category');
        return view('admin.product-catalog.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_catalogs,name',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
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
        $categories = ProductCatalog::distinct()->pluck('category');
        return view('admin.product-catalog.edit', compact('productCatalog', 'categories'));
    }

    public function update(Request $request, ProductCatalog $productCatalog)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_catalogs,name,' . $productCatalog->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
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
                return $query->byCategory($category);
            })
            ->limit(20)
            ->get(['id', 'name', 'description', 'category', 'unit', 'sku']);

        return response()->json($products);
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.name' => 'required|string|max:255',
            'products.*.category' => 'required|string|max:100',
            'products.*.unit' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $imported = 0;
            $skipped = 0;

            foreach ($request->products as $productData) {
                // Check if product already exists
                $existing = ProductCatalog::where('name', $productData['name'])->first();
                
                if (!$existing) {
                    ProductCatalog::create([
                        'name' => $productData['name'],
                        'description' => $productData['description'] ?? null,
                        'category' => $productData['category'],
                        'unit' => $productData['unit'],
                        'sku' => $productData['sku'] ?? null,
                        'specifications' => $productData['specifications'] ?? null,
                    ]);
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $message = "Imported {$imported} products successfully!";
            if ($skipped > 0) {
                $message .= " {$skipped} duplicates skipped.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to import products: ' . $e->getMessage());
        }
    }

public function bulkImportStore(Request $request)
{
    $request->validate([
        'products' => 'required|array',
        'products.*.name' => 'required|string|max:255',
        'products.*.category' => 'required|string|max:100',
        'products.*.unit' => 'required|string|max:50',
    ]);

    DB::beginTransaction();
    try {
        $imported = 0;
        $skipped = 0;

        foreach ($request->products as $productData) {
            // Check if product already exists
            $existing = ProductCatalog::where('name', $productData['name'])->first();
            
            if (!$existing) {
                ProductCatalog::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'] ?? null,
                    'category' => $productData['category'],
                    'unit' => $productData['unit'],
                    'sku' => $productData['sku'] ?? null,
                    'specifications' => $productData['specifications'] ?? null,
                ]);
                $imported++;
            } else {
                $skipped++;
            }
        }

        DB::commit();

        $message = "Imported {$imported} products successfully!";
        if ($skipped > 0) {
            $message .= " {$skipped} duplicates skipped.";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to import products: ' . $e->getMessage()
        ], 500);
    }
}
}