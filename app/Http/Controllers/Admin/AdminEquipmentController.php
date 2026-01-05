<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminEquipmentController extends Controller
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

        // Get statistics
        $stats = [
            'total_count' => Equipment::count(),
            'total_value' => Equipment::sum('value'),
            'active_count' => Equipment::where('status', 'active')->count(),
            'active_value' => Equipment::where('status', 'active')->sum('value'),
        ];

        $categories = Equipment::getCategories();
        $statuses = Equipment::getStatuses();
        $projects = Project::orderBy('name')->get();

        return view('admin.equipments.index', compact(
            'equipments',
            'stats',
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

        return view('admin.equipments.show', compact('equipment'));
    }

    /**
     * Show the form for editing the specified equipment.
     */
    public function edit(Equipment $equipment)
    {
        $categories = Equipment::getCategories();
        $statuses = Equipment::getStatuses();
        $conditions = Equipment::getConditions();
        $projects = Project::orderBy('name')->get();

        return view('admin.equipments.edit', compact(
            'equipment',
            'categories',
            'statuses',
            'conditions',
            'projects'
        ));
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(Equipment::getCategories())),
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'condition' => 'required|string|in:' . implode(',', array_keys(Equipment::getConditions())),
            'location' => 'nullable|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'serial_number' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_keys(Equipment::getStatuses())),
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_images' => 'nullable|array',
        ]);

        // Handle image removal
        $currentImages = $equipment->images ?? [];
        if ($request->filled('remove_images')) {
            foreach ($request->remove_images as $imageToRemove) {
                $key = array_search($imageToRemove, $currentImages);
                if ($key !== false) {
                    Storage::disk('public')->delete($imageToRemove);
                    unset($currentImages[$key]);
                }
            }
            $currentImages = array_values($currentImages);
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('equipments', 'public');
                $currentImages[] = $path;
            }
        }

        $equipment->update([
            'name' => $validated['name'],
            'model' => $validated['model'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'value' => $validated['value'],
            'purchase_date' => $validated['purchase_date'] ?? null,
            'condition' => $validated['condition'],
            'location' => $validated['location'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'serial_number' => $validated['serial_number'] ?? null,
            'status' => $validated['status'],
            'images' => $currentImages,
        ]);

        return redirect()
            ->route('admin.equipments.show', $equipment)
            ->with('success', 'Equipment updated successfully.');
    }
}
