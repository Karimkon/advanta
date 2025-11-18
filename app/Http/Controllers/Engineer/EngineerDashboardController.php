<?php

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngineerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get projects assigned to this engineer
        $projects = $user->projects()->count();
        
        // Get requisitions created by this engineer
        $requisitions = Requisition::where('requested_by', $user->id)->count();
        $pendingRequisitions = Requisition::where('requested_by', $user->id)
            ->where('status', 'pending')
            ->count();
        $approvedRequisitions = Requisition::where('requested_by', $user->id)
            ->whereIn('status', ['project_manager_approved', 'operations_approved', 'procurement', 'ceo_approved', 'in_progress'])
            ->count();
        
        // Recent requisitions with actual amounts
        $recentRequisitions = Requisition::where('requested_by', $user->id)
            ->with(['project', 'items', 'lpo.receivedItems'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($requisition) {
                // Calculate actual amount based on received quantities
                $requisition->actual_amount = $this->calculateActualAmount($requisition);
                return $requisition;
            });

        return view('engineer.dashboard', compact(
            'projects',
            'requisitions',
            'pendingRequisitions',
            'approvedRequisitions',
            'recentRequisitions'
        ));
    }

    /**
     * Calculate actual amount based on received quantities
     */
    private function calculateActualAmount($requisition)
    {
        if (!$requisition->lpo || !$requisition->lpo->receivedItems) {
            return $requisition->estimated_total;
        }

        $actualAmount = 0;
        
        foreach ($requisition->lpo->receivedItems as $receivedItem) {
            if ($receivedItem->lpoItem && $receivedItem->quantity_received > 0) {
                $actualAmount += $receivedItem->quantity_received * $receivedItem->lpoItem->unit_price;
            }
        }

        return $actualAmount > 0 ? $actualAmount : $requisition->estimated_total;
    }
}