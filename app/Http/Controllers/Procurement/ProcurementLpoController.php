<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\Requisition;
use App\Models\RequisitionApproval;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StoreService;

class ProcurementLpoController extends Controller
{
    public function index()
    {
        $lpos = Lpo::with(['requisition', 'supplier', 'items'])
            ->latest()
            ->paginate(10);

        return view('procurement.lpos.index', compact('lpos'));
    }

    public function show(Lpo $lpo)
    {
        $lpo->load([
            'requisition',
            'requisition.project',
            'requisition.requester',
            'supplier',
            'items',
            'preparer',
            'issuer',
        ]);

           // Debug log to check the loaded data
    \Log::info('LPO Show Data:', [
        'lpo_id' => $lpo->id,
        'prepared_by' => $lpo->prepared_by,
        'preparer_loaded' => $lpo->relationLoaded('preparer'),
        'preparer_name' => $lpo->preparer ? $lpo->preparer->name : 'NULL',
        'all_relations' => $lpo->getRelations()
    ]);

        return view('procurement.lpos.show', compact('lpo'));
    }
}