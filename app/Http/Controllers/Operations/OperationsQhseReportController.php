<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\QhseReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OperationsQhseReportController extends Controller
{
    public function index(Request $request)
    {
        $query = QhseReport::latest();

        // Apply filters
        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('department')) {
            $query->where('department', 'like', '%' . $request->department . '%');
        }

        if ($request->filled('staff_name')) {
            $query->where('staff_name', 'like', '%' . $request->staff_name . '%');
        }

        $reports = $query->paginate(20);

        $stats = [
            'total' => QhseReport::count(),
            'safety' => QhseReport::where('report_type', 'safety')->count(),
            'quality' => QhseReport::where('report_type', 'quality')->count(),
            'companydocuments' => QhseReport::where('report_type', 'companydocuments')->count(),
            'health' => QhseReport::where('report_type', 'health')->count(),
            'environment' => QhseReport::where('report_type', 'environment')->count(),
            'incident' => QhseReport::where('report_type', 'incident')->count(),
            'today' => QhseReport::whereDate('created_at', today())->count(),
        ];

        return view('operations.qhse-reports.index', compact('reports', 'stats'));
    }

    public function show(QhseReport $qhseReport)
    {
        return view('operations.qhse-reports.show', compact('qhseReport'));
    }

    public function downloadAttachment(QhseReport $qhseReport, $index)
    {
        $attachments = $qhseReport->getAttachmentsArray();

        if (!isset($attachments[$index])) {
            abort(404);
        }

        $attachment = $attachments[$index];

        // Handle both formats: object with 'path' key or plain string
        if (is_array($attachment)) {
            $filePath = $attachment['path'] ?? '';
            $fileName = $attachment['name'] ?? basename($filePath);
        } else {
            $filePath = $attachment;
            $fileName = basename($attachment);
        }

        $path = storage_path('app/public/' . $filePath);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $fileName);
    }
}