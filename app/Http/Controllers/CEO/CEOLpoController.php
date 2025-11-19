<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Lpo;
use App\Models\Requisition;
use Illuminate\Http\Request;

class CEOLpoController extends Controller
{
    public function index()
    {
        // Get LPOs that need CEO approval (draft LPOs with requisitions in procurement status)
        $pendingLpos = Lpo::where('status', 'draft')
            ->whereHas('requisition', function($query) {
                $query->where('status', Requisition::STATUS_PROCUREMENT);
            })
            ->with(['requisition', 'requisition.project', 'supplier', 'items', 'preparer'])
            ->latest()
            ->paginate(10);

        // Get all LPOs for statistics and display
        $allLpos = Lpo::with(['requisition', 'supplier', 'items'])
            ->latest()
            ->get();

        return view('ceo.lpos.index', compact('pendingLpos', 'allLpos'));
    }

    public function show(Lpo $lpo)
    {
        // CEO can view any LPO
        $lpo->load([
            'requisition',
            'requisition.project',
            'requisition.requester',
            'supplier',
            'items',
            'preparer'
        ]);

        return view('ceo.lpos.show', compact('lpo'));
    }
}