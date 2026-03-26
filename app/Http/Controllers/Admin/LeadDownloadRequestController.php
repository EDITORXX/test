<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LeadDownloadReadyMail;
use App\Models\LeadDownloadRequest;
use App\Services\LeadExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LeadDownloadRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = LeadDownloadRequest::with(['requester.role', 'reviewer'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return view('admin.lead-download-requests.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'statuses' => [
                LeadDownloadRequest::STATUS_PENDING,
                LeadDownloadRequest::STATUS_APPROVED,
                LeadDownloadRequest::STATUS_PROCESSING,
                LeadDownloadRequest::STATUS_COMPLETED,
                LeadDownloadRequest::STATUS_REJECTED,
                LeadDownloadRequest::STATUS_EXPIRED,
            ],
            'currentStatus' => $status,
        ]);
    }

    public function approve(Request $request, LeadDownloadRequest $leadDownloadRequest, LeadExportService $leadExportService)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!in_array($leadDownloadRequest->status, [LeadDownloadRequest::STATUS_PENDING, LeadDownloadRequest::STATUS_APPROVED], true)) {
            return back()->with('error', 'Only pending or previously approved requests can be processed.');
        }

        DB::transaction(function () use ($leadDownloadRequest, $validated) {
            $leadDownloadRequest->update([
                'status' => LeadDownloadRequest::STATUS_PROCESSING,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'approved_at' => now(),
                'rejected_at' => null,
                'rejection_reason' => null,
                'admin_note' => $validated['admin_note'] ?? null,
            ]);
        });

        try {
            $export = $leadExportService->generateLeadExportFile(
                $leadDownloadRequest->requester,
                $leadDownloadRequest->filters ?? [],
                $leadDownloadRequest->fields ?? [],
                $leadDownloadRequest->format
            );

            $leadDownloadRequest->update([
                'status' => LeadDownloadRequest::STATUS_COMPLETED,
                'actual_format' => $export['actual_format'],
                'file_disk' => $export['disk'],
                'file_path' => $export['path'],
                'file_name' => $export['file_name'],
                'file_mime' => $export['mime_type'],
                'exported_records_count' => $export['record_count'],
                'processed_at' => now(),
                'expires_at' => now()->addDays(7),
            ]);

            try {
                Mail::to($leadDownloadRequest->requester->email)->send(new LeadDownloadReadyMail($leadDownloadRequest));
            } catch (\Throwable $mailThrowable) {
                return back()->with('success', 'Lead download approved and export generated. Email delivery failed, but the file is available in the ASM portal.');
            }

            return back()->with('success', 'Lead download approved and export generated successfully.');
        } catch (\Throwable $throwable) {
            $leadDownloadRequest->update([
                'status' => LeadDownloadRequest::STATUS_APPROVED,
                'admin_note' => trim(($validated['admin_note'] ?? '') . "\nGeneration failed: " . $throwable->getMessage()),
            ]);

            return back()->with('error', 'Approval saved, but export generation failed: ' . $throwable->getMessage());
        }
    }

    public function reject(Request $request, LeadDownloadRequest $leadDownloadRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!in_array($leadDownloadRequest->status, [LeadDownloadRequest::STATUS_PENDING, LeadDownloadRequest::STATUS_APPROVED, LeadDownloadRequest::STATUS_PROCESSING], true)) {
            return back()->with('error', 'This request can no longer be rejected.');
        }

        $leadDownloadRequest->update([
            'status' => LeadDownloadRequest::STATUS_REJECTED,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejected_at' => now(),
            'admin_note' => $validated['admin_note'] ?? null,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Lead download request rejected.');
    }
}
