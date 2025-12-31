<?php

namespace App\Http\Controllers;

use App\Models\QhseReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QhseReportController extends Controller
{
    // Public form for QHSE reports
    public function create()
    {
        return view('qhse-reports.create');
    }

    // Store QHSE report
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:safety,quality,companydocuments,health,environment,incident',            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'required|email|max:255',
            'access_code' => 'required|string',
            'report_date' => 'required|date',
            'location' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        // Verify access code
        $validAccessCode = config('app.qhse_access_code', 'QHSE2024');
        
        if ($validated['access_code'] !== $validAccessCode) {
            return back()->with('error', 'Invalid access code. Please check with QHSE department.')->withInput();
        }

        DB::beginTransaction();
        try {
            $attachments = [];
            
            // Handle file uploads
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('qhse-reports', $filename, 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType()
                    ];
                }
            }

            $report = QhseReport::create([
                'report_type' => $validated['report_type'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'staff_name' => $validated['staff_name'],
                'staff_email' => $validated['staff_email'],
                'access_code' => $validated['access_code'],
                'attachments' => $attachments,
                'report_date' => $validated['report_date'],
                'location' => $validated['location'],
                'department' => $validated['department'],
            ]);

            DB::commit();

            return redirect()->route('qhse-reports.success')
                ->with('success', 'QHSE Report submitted successfully! Reference: ' . $report->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit report: ' . $e->getMessage())->withInput();
        }
    }

    // Success page
    public function success()
    {
        return view('qhse-reports.success');
    }

    // Admin/CEO view of all QHSE reports
    public function index(Request $request)
    {
        $query = QhseReport::latest();

        // Apply filters
        if ($request->report_type) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->date) {
            $query->where('report_date', $request->date);
        }

        if ($request->location) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $reports = $query->paginate(15);

        return view('qhse-reports.index', compact('reports'));
    }

    // Show individual QHSE report
    public function show(QhseReport $qhseReport)
    {
        return view('qhse-reports.show', compact('qhseReport'));
    }

    // Download attachment
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

    // Delete QHSE report (admin only)
    public function destroy(QhseReport $qhseReport)
    {
        // Delete attached files
        $attachments = $qhseReport->getAttachmentsArray();
        foreach ($attachments as $attachment) {
            $filePath = is_array($attachment) ? ($attachment['path'] ?? '') : $attachment;
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
        }

        $qhseReport->delete();

        return redirect()->route('qhse-reports.index')
            ->with('success', 'QHSE Report deleted successfully!');
    }
}