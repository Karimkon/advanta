<?php
// app/Http/Controllers/Admin/ProductCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = ProductCategory::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.product-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ProductCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        $parentCategories = ProductCategory::whereNull('parent_id')
            ->where('id', '!=', $productCategory->id)
            ->orderBy('name')
            ->get();

        return view('admin.product-categories.edit', compact('productCategory', 'parentCategories'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name,' . $productCategory->id,
            'parent_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $productCategory->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if (!$productCategory->canBeDeleted()) {
            return redirect()->route('admin.product-categories.index')
                ->with('error', 'Cannot delete category. It has associated products or subcategories.');
        }

        $productCategory->delete();

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}