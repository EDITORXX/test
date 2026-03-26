<?php

namespace App\Http\Controllers;

use App\Models\LeadDownloadRequest;
use App\Services\LeadExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SalesManagerLeadDownloadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if (!$user || (!$user->isSalesManager() && !$user->isSeniorManager() && !$user->isAssistantSalesManager())) {
                abort(403, 'Unauthorized.');
            }

            return $next($request);
        });
    }

    public function index(LeadExportService $leadExportService)
    {
        $user = auth()->user();

        return view('sales-manager.lead-downloads.index', [
            'statuses' => $leadExportService->getAvailableStatuses(),
            'leadTypes' => $leadExportService->getLeadTypeOptions(),
            'dateRanges' => $leadExportService->getDateRangeOptions(),
            'fields' => $leadExportService->getFieldLabels(),
            'interestedProjects' => $leadExportService->getInterestedProjects(),
            'availableUsers' => $leadExportService->getAvailableAssigneesFor($user),
            'requests' => LeadDownloadRequest::with(['reviewer'])
                ->where('requested_by', $user->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request, LeadExportService $leadExportService)
    {
        $fieldKeys = array_keys($leadExportService->getFieldLabels());
        $dateRanges = array_keys($leadExportService->getDateRangeOptions());
        $leadTypeKeys = array_keys($leadExportService->getLeadTypeOptions());
        $statusKeys = $leadExportService->getAvailableStatuses();

        $validated = $request->validate([
            'format' => ['required', Rule::in(['csv', 'pdf'])],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => [Rule::in($fieldKeys)],
            'status' => ['nullable', 'array'],
            'status.*' => [Rule::in($statusKeys)],
            'lead_type' => ['nullable', 'array'],
            'lead_type.*' => [Rule::in($leadTypeKeys)],
            'interested_projects' => ['nullable', 'array'],
            'interested_projects.*' => ['integer', 'exists:interested_project_names,id'],
            'date_range' => ['required', Rule::in($dateRanges)],
            'from_date' => ['nullable', 'date', 'required_if:date_range,custom'],
            'to_date' => ['nullable', 'date', 'required_if:date_range,custom', 'after_or_equal:from_date'],
            'assigned_scope' => ['required', Rule::in(['own', 'my_team', 'specific_user'])],
            'user_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $filters = $leadExportService->sanitizeFiltersForUser(auth()->user(), $validated);
        $fields = $leadExportService->sanitizeFields($validated['fields']);

        LeadDownloadRequest::create([
            'requested_by' => auth()->id(),
            'status' => LeadDownloadRequest::STATUS_PENDING,
            'format' => $validated['format'],
            'filters' => $filters,
            'fields' => $fields,
        ]);

        return redirect()
            ->route('sales-manager.lead-downloads.index')
            ->with('success', 'Lead download request submitted for admin approval.');
    }

    public function download(LeadDownloadRequest $leadDownloadRequest)
    {
        $user = auth()->user();

        abort_unless($leadDownloadRequest->requested_by === $user->id, 403);

        if ($leadDownloadRequest->expires_at && $leadDownloadRequest->expires_at->isPast()) {
            $leadDownloadRequest->update(['status' => LeadDownloadRequest::STATUS_EXPIRED]);
            return back()->with('error', 'This download link has expired. Please submit a fresh request.');
        }

        if (!$leadDownloadRequest->isDownloadReady()) {
            return back()->with('error', 'This export file is not available for download yet.');
        }

        if (!Storage::disk($leadDownloadRequest->file_disk ?? 'local')->exists($leadDownloadRequest->file_path)) {
            return back()->with('error', 'The export file could not be found on the server.');
        }

        return Storage::disk($leadDownloadRequest->file_disk ?? 'local')->download(
            $leadDownloadRequest->file_path,
            $leadDownloadRequest->file_name
        );
    }
}
