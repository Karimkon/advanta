<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Lpo;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreLpoController extends Controller
{
    /**
     * Show LPOs awaiting delivery confirmation
     */
    public function index(Store $store)
    {
        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $lpos = Lpo::whereHas('requisition', function($query) use ($store) {
                $query->where('project_id', $store->project_id);
            })
            ->where('status', 'issued')
            ->with(['requisition', 'requisition.project', 'supplier'])
            ->latest()
            ->paginate(20);

        $stores = Store::all();

        return view('stores.lpos.index', compact('store', 'lpos', 'stores'));
    }

    /**
     * Show form to confirm LPO delivery
     */
    public function confirmDelivery(Store $store, Lpo $lpo)
    {
        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        // Verify LPO belongs to this store's project
        if ($lpo->requisition->project_id !== $store->project_id) {
            abort(403, 'This LPO does not belong to your project store.');
        }

        if ($lpo->status !== 'issued') {
            abort(403, 'Only issued LPOs can be confirmed as delivered.');
        }

        $lpo->load(['items', 'requisition', 'supplier']);

        return view('stores.lpos.confirm-delivery', compact('store', 'lpo'));
    }

    /**
     * Process LPO delivery confirmation
     */
    public function processDelivery(Request $request, Store $store, Lpo $lpo)
    {
        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        if ($lpo->status !== 'issued') {
            abort(403, 'Only issued LPOs can be confirmed as delivered.');
        }

        // Validate received quantities
        $request->validate([
            'received_items' => 'required|array',
            'received_items.*.quantity_received' => 'required|numeric|min:0',
            'received_items.*.condition' => 'nullable|string|max:255',
            'delivery_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update LPO status
            $lpo->update([
                'status' => 'delivered',
                'delivery_date' => now(),
                'delivery_notes' => $request->delivery_notes,
            ]);

            // Update requisition status to DELIVERED (store has received items)
            $lpo->requisition->update([
                'status' => Requisition::STATUS_DELIVERED
            ]);

            // Process LPO items to store inventory based on ACTUAL received quantities
            $storeService = new StoreService();
            $receivedItems = collect($request->received_items);
            
            $result = $storeService->processDeliveredLpo($lpo, $receivedItems);

            // Create approval record
            RequisitionApproval::create([
                'requisition_id' => $lpo->requisition_id,
                'approved_by' => auth()->id(),
                'role' => 'stores',
                'action' => 'delivery_confirmed',
                'comment' => 'Items delivered and received in store for LPO: ' . $lpo->lpo_number . '. ' . ($request->delivery_notes ? 'Notes: ' . $request->delivery_notes : ''),
            ]);

            DB::commit();

            return redirect()->route('stores.lpos.index', $store)
                ->with('success', 'LPO delivery confirmed! Items added to store inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to confirm delivery: ' . $e->getMessage());
        }
    }

    /**
     * Show delivered LPOs
     */
    public function delivered(Store $store)
    {
        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        $lpos = Lpo::whereHas('requisition', function($query) use ($store) {
                $query->where('project_id', $store->project_id);
            })
            ->where('status', 'delivered')
            ->with(['requisition', 'requisition.project', 'supplier'])
            ->latest()
            ->paginate(20);

        $stores = Store::all();

        return view('stores.lpos.delivered', compact('store', 'lpos', 'stores'));
    }

    /**
     * Show LPO details
     */
    public function show(Store $store, Lpo $lpo)
    {
        // Authorization check
        if (!$this->canAccessStore(auth()->user(), $store)) {
            abort(403, 'Unauthorized access to this store.');
        }

        // Verify LPO belongs to this store's project
        if ($lpo->requisition->project_id !== $store->project_id) {
            abort(403, 'This LPO does not belong to your project store.');
        }

        $lpo->load(['items', 'requisition', 'requisition.project', 'supplier', 'preparer']);

        return view('stores.lpos.show', compact('store', 'lpo'));
    }

    /**
     * Check if user can access the store
     */
    private function canAccessStore($user, $store)
    {
        // Main store manager (ID 6) can access main store
        if ($user->id === 6 && $store->isMainStore()) {
            return true;
        }

        // Project store users can access their project stores
        if ($store->isProjectStore() && $store->project) {
            return $store->project->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}