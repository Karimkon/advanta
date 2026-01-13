<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\LpoItem;
use App\Models\Procurement;
use App\Exports\LposExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminLpoController extends Controller
{
    public function index()
    {
        $lpos = Lpo::with(['supplier', 'requisition.project', 'items'])
            ->latest()
            ->get();

        return view('admin.lpos.index', compact('lpos'));
    }

    public function show(Lpo $lpo)
    {
        $lpo->load(['supplier', 'requisition.project', 'requisition.requester', 'items', 'preparer']);
        return view('admin.lpos.show', compact('lpo'));
    }

    public function create(Procurement $procurement)
    {
        return view('admin.lpos.create', compact('procurement'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'procurement_id' => 'required|exists:procurements,id',
            'lpo_number' => 'required|unique:lpos',
            'issue_date' => 'required|date',
            'status' => 'required'
        ]);

        Lpo::create($request->all());

        return redirect()->route('admin.lpos.index')
            ->with('success', 'LPO Issued');
    }

    /**
     * Show the fix page for an LPO
     */
    public function fix(Lpo $lpo)
    {
        $lpo->load(['supplier', 'requisition.project', 'requisition.requester', 'items']);
        return view('admin.lpos.fix', compact('lpo'));
    }

    /**
     * Fix status of LPO or Requisition
     */
    public function fixStatus(Request $request, Lpo $lpo)
    {
        DB::beginTransaction();
        try {
            $messages = [];

            // Fix requisition status
            if ($request->has('requisition_status') && $lpo->requisition) {
                $oldStatus = $lpo->requisition->status;
                $lpo->requisition->update(['status' => $request->requisition_status]);
                $messages[] = "Requisition status changed from '{$oldStatus}' to '{$request->requisition_status}'";
            }

            // Fix LPO status
            if ($request->has('lpo_status')) {
                $oldStatus = $lpo->status;
                $lpo->update(['status' => $request->lpo_status]);
                $messages[] = "LPO status changed from '{$oldStatus}' to '{$request->lpo_status}'";
            }

            DB::commit();

            return redirect()->route('admin.lpos.fix', $lpo)
                ->with('success', implode('. ', $messages));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to fix status: ' . $e->getMessage());
        }
    }

    /**
     * Update LPO item prices
     */
    public function updatePrices(Request $request, Lpo $lpo)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $vatAmount = 0;

            foreach ($request->items as $itemId => $itemData) {
                $item = LpoItem::find($itemId);
                if ($item && $item->lpo_id === $lpo->id) {
                    $quantity = floatval($itemData['quantity']);
                    $unitPrice = floatval($itemData['unit_price']);
                    $totalPrice = $quantity * $unitPrice;

                    $item->update([
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ]);

                    $subtotal += $totalPrice;

                    // Calculate VAT if applicable
                    if ($item->has_vat) {
                        $vatAmount += $totalPrice * ($item->vat_rate / 100);
                    }
                }
            }

            // Update LPO totals
            $lpo->update([
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total' => $subtotal + $vatAmount,
            ]);

            // Also update requisition items if they exist
            if ($lpo->requisition) {
                foreach ($request->items as $itemId => $itemData) {
                    $lpoItem = LpoItem::find($itemId);
                    if ($lpoItem) {
                        // Find corresponding requisition item by description
                        $reqItem = $lpo->requisition->items()
                            ->where('name', $lpoItem->description)
                            ->first();

                        if ($reqItem) {
                            $reqItem->update([
                                'quantity' => floatval($itemData['quantity']),
                                'unit_price' => floatval($itemData['unit_price']),
                                'total_price' => floatval($itemData['quantity']) * floatval($itemData['unit_price']),
                            ]);
                        }
                    }
                }

                // Update requisition total
                $lpo->requisition->update([
                    'estimated_total' => $subtotal,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.lpos.fix', $lpo)
                ->with('success', 'Prices updated successfully! New total: UGX ' . number_format($subtotal + $vatAmount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update prices: ' . $e->getMessage());
        }
    }

    /**
     * Export LPOs to Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'supplier_id']);
        return Excel::download(new LposExport($filters), 'lpos_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export LPOs to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Lpo::with(['supplier', 'requisition.project', 'items', 'issuer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lpos = $query->latest()->get();

        $pdf = Pdf::loadView('exports.pdf.lpos', [
            'lpos' => $lpos,
            'title' => 'LPOs Report'
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('lpos_' . date('Y-m-d') . '.pdf');
    }
}
