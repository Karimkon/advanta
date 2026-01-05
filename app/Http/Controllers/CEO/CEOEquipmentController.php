<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Project;
use Illuminate\Http\Request;

class CEOEquipmentController extends Controller
{
    /**
     * Display a listing of all equipments.
     */
    public function index(Request $request)
    {
        $query = Equipment::with(['project', 'addedBy']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Search by name or model
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $equipments = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get comprehensive statistics
        $stats = [
            'total_count' => Equipment::count(),
            'total_value' => Equipment::sum('value'),
            'active_count' => Equipment::where('status', 'active')->count(),
            'active_value' => Equipment::where('status', 'active')->sum('value'),
            'maintenance_count' => Equipment::where('status', 'maintenance')->count(),
            'disposed_count' => Equipment::where('status', 'disposed')->count(),
        ];

        // Get value by category
        $valueByCategory = Equipment::where('status', 'active')
            ->selectRaw('category, SUM(value) as total_value, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category => [
                    'value' => $item->total_value,
                    'count' => $item->count,
                ]];
            });

        $categories = Equipment::getCategories();
        $statuses = Equipment::getStatuses();
        $projects = Project::orderBy('name')->get();

        return view('ceo.equipments.index', compact(
            'equipments',
            'stats',
            'valueByCategory',
            'categories',
            'statuses',
            'projects'
        ));
    }

    /**
     * Display the specified equipment.
     */
    public function show(Equipment $equipment)
    {
        $equipment->load(['project', 'addedBy']);

        return view('ceo.equipments.show', compact('equipment'));
    }
}
