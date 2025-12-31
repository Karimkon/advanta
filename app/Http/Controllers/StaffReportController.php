<?php

namespace App\Http\Controllers;

use App\Models\StaffReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffReportController extends Controller
{
    // Public form for staff to submit reports
    public function create()
    {
        return view('staff-reports.create');
    }

    // Store report with access code authentication
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:daily,weekly',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'required|email|max:255',
            'access_code' => 'required|string',
            'report_date' => 'required|date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        // Verify access code (you can set this in .env or config)
        $validAccessCode = config('app.staff_access_code', 'ADVANTA2024');
        
        if ($validated['access_code'] !== $validAccessCode) {
            return back()->with('error', 'Invalid access code. Please check with your supervisor.')->withInput();
        }

        DB::beginTransaction();
        try {
            $attachments = [];
            
            // Handle file uploads
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::random(20) . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('staff-reports', $filename, 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType()
                    ];
                }
            }

            $report = StaffReport::create([
                'report_type' => $validated['report_type'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'staff_name' => $validated['staff_name'],
                'staff_email' => $validated['staff_email'],
                'access_code' => $validated['access_code'],
                'attachments' => $attachments,
                'report_date' => $validated['report_date'],
            ]);

            DB::commit();

            return redirect()->route('staff-reports.success')
                ->with('success', 'Report submitted successfully! Reference: ' . $report->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit report: ' . $e->getMessage())->withInput();
        }
    }

    // Success page after submission
    public function success()
    {
        return view('staff-reports.success');
    }

    // Admin/CEO view of all reports
    public function index(Request $request)
    {
        $query = StaffReport::latest();

        // Apply filters
        if ($request->report_type) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->date) {
            $query->where('report_date', $request->date);
        }

        $reports = $query->paginate(15);

        return view('staff-reports.index', compact('reports'));
    }

    // Show individual report
    public function show(StaffReport $staffReport)
    {
        return view('staff-reports.show', compact('staffReport'));
    }

    // Download attachment
    public function downloadAttachment(StaffReport $staffReport, $index)
    {
        $attachments = $staffReport->getAttachmentsArray();

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

    // Delete report (admin only)
    public function destroy(StaffReport $staffReport)
    {
        // Delete attached files
        $attachments = $staffReport->getAttachmentsArray();
        foreach ($attachments as $attachment) {
            $filePath = is_array($attachment) ? ($attachment['path'] ?? '') : $attachment;
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
        }

        $staffReport->delete();

        return redirect()->route('staff-reports.index')
            ->with('success', 'Report deleted successfully!');
    }
}