<?php

namespace App\Http\Controllers\Subcontractor;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Store;
use App\Models\ProductCatalog;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubcontractorRequisitionController extends Controller
{
    public function index()
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        $requisitions = $subcontractor->requisitions()
            ->with(['project', 'items', 'store'])
            ->latest()
            ->paginate(20);

        return view('subcontractor.requisitions.index', compact('subcontractor', 'requisitions'));
    }

    public function create()
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        $activeContracts = $subcontractor->projectSubcontractors()
            ->where('status', 'active')
            ->with(['project.stores'])
            ->get();

        if ($activeContracts->isEmpty()) {
            return back()->with('error', 'You have no active project contracts. Please contact administration.');
        }

        $products = ProductCatalog::orderBy('name')->get();

        return view('subcontractor.requisitions.create', compact(
            'subcontractor',
            'activeContracts',
            'products'
        ));
    }

    public function store(Request $request)
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type' => 'required|in:store,purchase',
            'urgency' => 'required|in:low,medium,high',
            'reason' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
        ]);

        if (!$subcontractor->hasActiveContractOn($request->project_id)) {
            return back()->with('error', 'You do not have an active contract on this project.');
        }

        DB::beginTransaction();
        try {
            $type = $request->type === 'store' ? 'STR' : 'PUR';
            $ref = $type . '-SUB-' . date('Ymd') . '-' . str_pad(
                Requisition::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $storeId = null;
            if ($request->type === 'store') {
                $store = Store::where('project_id', $request->project_id)
                    ->where('type', 'project')
                    ->first();
                $storeId = $store?->id;
            }

            $estimatedTotal = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['estimated_unit_price'];
            });

            $requisition = Requisition::create([
                'ref' => $ref,
                'project_id' => $request->project_id,
                'subcontractor_id' => $subcontractor->id,
                'requested_by' => null,
                'type' => $request->type,
                'store_id' => $storeId,
                'urgency' => $request->urgency,
                'status' => Requisition::STATUS_PENDING,
                'estimated_total' => $estimatedTotal,
                'reason' => $request->reason,
            ]);

            foreach ($request->items as $item) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'],
                    'product_catalog_id' => $item['product_catalog_id'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('subcontractor.requisitions.show', $requisition)
                ->with('success', 'Requisition created successfully! Ref: ' . $ref);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create requisition: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Requisition $requisition)
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        if ($requisition->subcontractor_id !== $subcontractor->id) {
            abort(403, 'You do not have access to this requisition.');
        }

        $requisition->load(['project', 'items', 'store', 'approvals.user', 'lpo']);

        return view('subcontractor.requisitions.show', compact('subcontractor', 'requisition'));
    }

    public function edit(Requisition $requisition)
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        if ($requisition->subcontractor_id !== $subcontractor->id) {
            abort(403, 'You do not have access to this requisition.');
        }

        if (!$requisition->canBeEdited()) {
            return back()->with('error', 'This requisition cannot be edited anymore.');
        }

        $activeContracts = $subcontractor->projectSubcontractors()
            ->where('status', 'active')
            ->with(['project.stores'])
            ->get();

        $products = ProductCatalog::orderBy('name')->get();

        $requisition->load(['items']);

        return view('subcontractor.requisitions.edit', compact(
            'subcontractor',
            'requisition',
            'activeContracts',
            'products'
        ));
    }

    public function update(Request $request, Requisition $requisition)
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        if ($requisition->subcontractor_id !== $subcontractor->id) {
            abort(403, 'You do not have access to this requisition.');
        }

        if (!$requisition->canBeEdited()) {
            return back()->with('error', 'This requisition cannot be edited anymore.');
        }

        $request->validate([
            'urgency' => 'required|in:low,medium,high',
            'reason' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.estimated_unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $estimatedTotal = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['estimated_unit_price'];
            });

            $requisition->update([
                'urgency' => $request->urgency,
                'estimated_total' => $estimatedTotal,
                'reason' => $request->reason,
            ]);

            $requisition->items()->delete();

            foreach ($request->items as $item) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'estimated_unit_price' => $item['estimated_unit_price'],
                    'product_catalog_id' => $item['product_catalog_id'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('subcontractor.requisitions.show', $requisition)
                ->with('success', 'Requisition updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update requisition: ' . $e->getMessage())->withInput();
        }
    }

    public function searchProducts(Request $request)
    {
        $search = $request->q;
        $category = $request->category;
        $storeId = $request->store_id;

        // If store_id is provided, search only within that store's inventory
        if ($storeId) {
            $query = \App\Models\InventoryItem::where('store_id', $storeId)
                ->where('quantity', '>', 0);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhereHas('productCatalog', function($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                      });
                });
            }

            if ($category) {
                $query->where('category', $category);
            }

            $items = $query->with('productCatalog')->limit(50)->get();

            $results = $items->map(function($item) {
                return [
                    'id' => 'inv_' . $item->id, // Unique ID for Select2
                    'text' => $item->name . ($item->sku ? " ({$item->sku})" : ''),
                    'name' => $item->name,
                    'description' => $item->productCatalog?->description ?? '',
                    'category' => $item->category,
                    'unit' => $item->unit,
                    'price' => $item->unit_price,
                    'available_stock' => $item->quantity,
                    'has_stock' => $item->quantity > 0,
                    'inventory_item_id' => $item->id,
                    'product_catalog_id' => $item->product_catalog_id // Can be null
                ];
            });

            return response()->json(['results' => $results]);
        }

        // For purchase requisitions, search all products from catalog
        $query = ProductCatalog::active();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->limit(50)->get();

        $results = $products->map(function($product) {
            $totalStock = $product->inventoryItems
                ->where('quantity', '>', 0)
                ->sum('quantity');

            return [
                'id' => $product->id,
                'text' => $product->name . ($product->sku ? " ({$product->sku})" : ''),
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category,
                'unit' => $product->unit,
                'available_stock' => $totalStock,
                'has_stock' => $totalStock > 0
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function destroy(Requisition $requisition)
    {
        $subcontractor = Auth::guard('subcontractor')->user();

        if ($requisition->subcontractor_id !== $subcontractor->id) {
            abort(403, 'You do not have access to this requisition.');
        }

        if (!$requisition->canBeDeleted()) {
            return back()->with('error', 'This requisition cannot be deleted.');
        }

        $requisition->items()->delete();
        $requisition->delete();

        return redirect()->route('subcontractor.requisitions.index')
            ->with('success', 'Requisition deleted successfully.');
    }
}
