<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use App\Models\StaffReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CEOStaffReportController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffReport::latest();

        // Apply filters
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        if ($request->filled('staff_name')) {
            $query->where('staff_name', 'like', '%' . $request->staff_name . '%');
        }

        $reports = $query->paginate(20);

        $stats = [
            'total' => StaffReport::count(),
            'daily' => StaffReport::where('report_type', 'daily')->count(),
            'weekly' => StaffReport::where('report_type', 'weekly')->count(),
            'today' => StaffReport::whereDate('created_at', today())->count(),
        ];

        return view('ceo.staff-reports.index', compact('reports', 'stats'));
    }

    public function show(StaffReport $staffReport)
    {
        return view('ceo.staff-reports.show', compact('staffReport'));
    }

    public function downloadAttachment(StaffReport $staffReport, $index)
    {
        $attachments = $staffReport->attachments;
        
        if (!isset($attachments[$index])) {
            abort(404);
        }

        $attachment = $attachments[$index];
        $path = storage_path('app/public/' . $attachment['path']);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $attachment['name']);
    }

    public function destroy(StaffReport $staffReport)
    {
        // Delete attached files
        if ($staffReport->attachments) {
            foreach ($staffReport->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $staffReport->delete();

        return redirect()->route('ceo.staff-reports.index')
            ->with('success', 'Report deleted successfully!');
    }
}