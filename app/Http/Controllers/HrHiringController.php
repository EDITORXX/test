<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrHiringController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->string('status')->toString();
        $search = trim((string) $request->string('search'));

        $baseQuery = $this->candidateQuery($user->id);

        $counts = [];
        foreach (Lead::hiringStatusOptions() as $key => $label) {
            $counts[$key] = (clone $baseQuery)->where('hiring_status', $key)->count();
        }

        $candidates = (clone $baseQuery)
            ->when($status !== '' && array_key_exists($status, Lead::hiringStatusOptions()), function (Builder $query) use ($status) {
                $query->where('hiring_status', $status);
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hr-manager.hiring.index', [
            'candidates' => $candidates,
            'counts' => $counts,
            'statusOptions' => Lead::hiringStatusOptions(),
            'selectedStatus' => $status,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Lead $lead): View
    {
        $candidate = $this->candidateQuery($request->user()->id)
            ->whereKey($lead->id)
            ->firstOrFail();

        return view('hr-manager.hiring.show', [
            'candidate' => $candidate,
            'statusOptions' => Lead::hiringStatusOptions(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $candidate = $this->candidateQuery($request->user()->id)
            ->whereKey($lead->id)
            ->firstOrFail();

        $data = $request->validate([
            'hiring_status' => 'required|in:' . implode(',', array_keys(Lead::hiringStatusOptions())),
            'hr_remark' => 'nullable|string',
        ]);

        $candidate->update([
            'hiring_status' => $data['hiring_status'],
            'hr_remark' => trim((string) ($data['hr_remark'] ?? '')) ?: null,
        ]);

        return redirect()
            ->route('hr-manager.hiring.show', $candidate)
            ->with('success', 'Candidate status updated successfully.');
    }

    protected function candidateQuery(int $userId): Builder
    {
        return Lead::query()
            ->with(['activeAssignments.assignedTo.role', 'latestFbLead.form.page'])
            ->where('is_hiring_candidate', true)
            ->whereHas('activeAssignments', function (Builder $query) use ($userId) {
                $query->where('assigned_to', $userId)
                    ->where('is_active', true);
            });
    }
}
