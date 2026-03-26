<?php

namespace App\Services;

use App\Models\InterestedProjectName;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LeadExportService
{
    public function getAvailableStatuses(): array
    {
        return [
            'new',
            'connected',
            'verified_prospect',
            'meeting_scheduled',
            'meeting_completed',
            'visit_scheduled',
            'visit_done',
            'revisited_scheduled',
            'revisited_completed',
            'closed',
            'dead',
            'on_hold',
        ];
    }

    public function getLeadTypeOptions(): array
    {
        return [
            'prospect' => 'Prospect',
            'visit' => 'Visit',
            'revisit' => 'Revisit',
            'meeting' => 'Meeting',
            'closer' => 'Closer',
        ];
    }

    public function getDateRangeOptions(): array
    {
        return [
            'all_time' => 'All Time',
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
            'this_year' => 'This Year',
            'custom' => 'Custom Range',
        ];
    }

    public function getFieldLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Customer Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'status' => 'Status',
            'budget' => 'Budget',
            'preferred_location' => 'Location',
            'source' => 'Lead Source',
            'assigned_to' => 'Assigned To',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'last_contacted_at' => 'Last Contacted',
            'notes' => 'Notes',
            'employee_remark' => 'Employee Remark',
            'manager_remark' => 'Manager Remark',
            'interested_projects' => 'Interested Projects',
            'dead_reason' => 'Dead Reason',
            'dead_at_stage' => 'Dead At Stage',
            'marked_dead_at' => 'Marked Dead Date',
            'marked_dead_by' => 'Marked Dead By',
        ];
    }

    public function getInterestedProjects()
    {
        return InterestedProjectName::where('is_active', true)->orderBy('name')->get();
    }

    public function getAvailableAssigneesFor(User $user): Collection
    {
        $accessibleIds = $this->getAccessibleAssigneeIds($user);

        return User::whereIn('id', $accessibleIds)
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->whereIn('slug', [
                    Role::SALES_MANAGER,
                    Role::SENIOR_MANAGER,
                    Role::ASSISTANT_SALES_MANAGER,
                    Role::SALES_EXECUTIVE,
                ]);
            })
            ->orderBy('name')
            ->get();
    }

    public function sanitizeFields(array $fields): array
    {
        $allowed = array_keys($this->getFieldLabels());
        $fields = array_values(array_intersect($fields, $allowed));

        return empty($fields) ? ['name', 'phone', 'status', 'assigned_to', 'created_at'] : $fields;
    }

    public function sanitizeFiltersForUser(User $user, array $filters): array
    {
        $accessibleIds = $this->getAccessibleAssigneeIds($user);
        $scope = $filters['assigned_scope'] ?? 'my_team';

        if (!in_array($scope, ['own', 'my_team', 'specific_user'], true)) {
            $scope = 'my_team';
        }

        $specificUserId = isset($filters['user_id']) ? (int) $filters['user_id'] : null;

        if ($scope === 'specific_user' && (!$specificUserId || !in_array($specificUserId, $accessibleIds, true))) {
            $scope = 'my_team';
            $specificUserId = null;
        }

        return [
            'status' => array_values(array_filter((array) ($filters['status'] ?? []))),
            'lead_type' => array_values(array_filter((array) ($filters['lead_type'] ?? []))),
            'interested_projects' => array_values(array_map('intval', array_filter((array) ($filters['interested_projects'] ?? [])))),
            'date_range' => $filters['date_range'] ?? 'all_time',
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
            'assigned_scope' => $scope,
            'user_id' => $specificUserId,
            'search' => trim((string) ($filters['search'] ?? '')),
        ];
    }

    public function buildLeadQueryForUser(User $user, array $filters): Builder
    {
        $filters = $this->sanitizeFiltersForUser($user, $filters);

        $query = Lead::with([
            'creator',
            'activeAssignments.assignedTo',
            'prospects.interestedProjects',
            'meetings',
            'siteVisits',
        ]);

        $accessibleIds = $this->getAccessibleAssigneeIds($user);

        if (empty($accessibleIds)) {
            return $query->whereRaw('1 = 0');
        }

        if (($filters['assigned_scope'] ?? 'my_team') === 'own') {
            $query->whereHas('activeAssignments', function ($assignmentQuery) use ($user) {
                $assignmentQuery->where('assigned_to', $user->id);
            });
        } elseif (($filters['assigned_scope'] ?? 'my_team') === 'specific_user' && $filters['user_id']) {
            $query->whereHas('activeAssignments', function ($assignmentQuery) use ($filters) {
                $assignmentQuery->where('assigned_to', $filters['user_id']);
            });
        } else {
            $query->whereHas('activeAssignments', function ($assignmentQuery) use ($accessibleIds) {
                $assignmentQuery->whereIn('assigned_to', $accessibleIds);
            });
        }

        if (!empty($filters['status'])) {
            $query->whereIn('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['lead_type'])) {
            $types = $filters['lead_type'];
            $query->where(function ($typeQuery) use ($types) {
                foreach ($types as $type) {
                    $typeQuery->orWhere(function ($subQuery) use ($type) {
                        if ($type === 'prospect') {
                            $subQuery->where('status', 'verified_prospect');
                        } elseif ($type === 'visit') {
                            $subQuery->whereIn('status', ['visit_scheduled', 'visit_done'])
                                ->whereHas('siteVisits', function ($visitQuery) {
                                    $visitQuery->where('lead_type', 'New Visit');
                                });
                        } elseif ($type === 'revisit') {
                            $subQuery->whereIn('status', ['revisited_scheduled', 'revisited_completed'])
                                ->whereHas('siteVisits', function ($visitQuery) {
                                    $visitQuery->where('lead_type', 'Revisited');
                                });
                        } elseif ($type === 'meeting') {
                            $subQuery->where(function ($meetingQuery) {
                                $meetingQuery->whereIn('status', ['meeting_scheduled', 'meeting_completed'])
                                    ->orWhereHas('meetings');
                            });
                        } elseif ($type === 'closer') {
                            $subQuery->whereHas('siteVisits', function ($closerQuery) {
                                $closerQuery->where(function ($statusQuery) {
                                    $statusQuery->where('closer_status', 'pending')
                                        ->orWhereNotNull('closer_status');
                                });
                            });
                        }
                    });
                }
            });
        }

        if (!empty($filters['interested_projects'])) {
            $query->whereHas('prospects.interestedProjects', function ($projectQuery) use ($filters) {
                $projectQuery->whereIn('interested_project_names.id', $filters['interested_projects']);
            });
        }

        $range = $this->resolveDateRange($filters['date_range'] ?? 'all_time', $filters['from_date'] ?? null, $filters['to_date'] ?? null);
        if ($range) {
            $query->whereBetween('created_at', [$range['start_date'], $range['end_date']]);
        }

        return $query;
    }

    public function generateLeadExportFile(User $user, array $filters, array $fields, string $format): array
    {
        $fields = $this->sanitizeFields($fields);
        $format = strtolower($format) === 'pdf' ? 'pdf' : 'csv';

        $leads = $this->buildLeadQueryForUser($user, $filters)->get();
        $count = $leads->count();

        if ($count === 0) {
            throw new \RuntimeException('No leads found matching the selected filters.');
        }

        if ($count > 10000) {
            throw new \RuntimeException('Export limit exceeded. Please refine the filters to 10,000 records or less.');
        }

        if ($format === 'pdf') {
            return $this->storeLeadPdf($leads, $fields);
        }

        return $this->storeLeadCsv($leads, $fields);
    }

    public function getAccessibleAssigneeIds(User $user): array
    {
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        if ($user->isAdmin() || $user->isCrm()) {
            return User::where('is_active', true)->pluck('id')->all();
        }

        if ($user->isSalesHead()) {
            return collect([$user->id])
                ->merge($user->getAllTeamMemberIds())
                ->unique()
                ->values()
                ->all();
        }

        if ($user->isSalesManager() || $user->isSeniorManager()) {
            return collect([$user->id])
                ->merge($user->teamMembers()->pluck('id')->all())
                ->unique()
                ->values()
                ->all();
        }

        return [$user->id];
    }

    private function storeLeadCsv(Collection $leads, array $fields): array
    {
        $headers = [];
        $fieldLabels = $this->getFieldLabels();

        foreach ($fields as $field) {
            if (isset($fieldLabels[$field])) {
                $headers[] = $fieldLabels[$field];
            }
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($leads as $lead) {
            $row = [];
            foreach ($fields as $field) {
                $row[] = $this->getLeadFieldValue($lead, $field);
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        $timestamp = now()->format('Ymd_His');
        $fileName = "asm_leads_{$timestamp}.csv";
        $relativePath = "lead-downloads/{$fileName}";
        Storage::disk('local')->put($relativePath, $contents);

        return [
            'path' => $relativePath,
            'disk' => 'local',
            'file_name' => $fileName,
            'mime_type' => 'text/csv; charset=UTF-8',
            'actual_format' => 'csv',
            'record_count' => $leads->count(),
        ];
    }

    private function storeLeadPdf(Collection $leads, array $fields): array
    {
        $fieldLabels = $this->getFieldLabels();
        $headers = [];

        foreach ($fields as $field) {
            if (isset($fieldLabels[$field])) {
                $headers[] = $fieldLabels[$field];
            }
        }

        $html = view('export.pdf.leads', compact('leads', 'headers', 'fields'))->render();
        $timestamp = now()->format('Ymd_His');

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $contents = $pdf->output();
            $fileName = "asm_leads_{$timestamp}.pdf";
            $relativePath = "lead-downloads/{$fileName}";
            Storage::disk('local')->put($relativePath, $contents);

            return [
                'path' => $relativePath,
                'disk' => 'local',
                'file_name' => $fileName,
                'mime_type' => 'application/pdf',
                'actual_format' => 'pdf',
                'record_count' => $leads->count(),
            ];
        }

        $fileName = "asm_leads_{$timestamp}.html";
        $relativePath = "lead-downloads/{$fileName}";
        Storage::disk('local')->put($relativePath, $html);

        return [
            'path' => $relativePath,
            'disk' => 'local',
            'file_name' => $fileName,
            'mime_type' => 'text/html; charset=UTF-8',
            'actual_format' => 'html',
            'record_count' => $leads->count(),
        ];
    }

    private function getLeadFieldValue(Lead $lead, string $field): string
    {
        return match ($field) {
            'assigned_to' => $lead->activeAssignments->first()?->assignedTo->name ?? 'Unassigned',
            'status' => ucfirst(str_replace('_', ' ', (string) $lead->status)),
            'created_at', 'updated_at', 'last_contacted_at', 'marked_dead_at' => $lead->{$field} ? $lead->{$field}->format('Y-m-d H:i') : 'N/A',
            'marked_dead_by' => optional(User::find($lead->marked_dead_by))->name ?? 'N/A',
            'interested_projects' => $this->formatInterestedProjects($lead),
            default => (string) ($lead->{$field} ?? 'N/A'),
        };
    }

    private function formatInterestedProjects(Lead $lead): string
    {
        $projects = collect();

        foreach ($lead->prospects as $prospect) {
            if ($prospect->interestedProjects) {
                $projects = $projects->merge($prospect->interestedProjects);
            }
        }

        $names = $projects->unique('id')->pluck('name');

        return $names->isNotEmpty() ? $names->implode(', ') : 'No Projects';
    }

    private function resolveDateRange(string $range, ?string $fromDate, ?string $toDate): ?array
    {
        $today = Carbon::today();

        return match ($range) {
            'today' => [
                'start_date' => $today->copy()->startOfDay(),
                'end_date' => $today->copy()->endOfDay(),
            ],
            'this_week' => [
                'start_date' => $today->copy()->startOfWeek(),
                'end_date' => $today->copy()->endOfWeek(),
            ],
            'this_month' => [
                'start_date' => $today->copy()->startOfMonth(),
                'end_date' => $today->copy()->endOfMonth(),
            ],
            'this_year' => [
                'start_date' => $today->copy()->startOfYear(),
                'end_date' => $today->copy()->endOfYear(),
            ],
            'custom' => $this->resolveCustomDateRange($fromDate, $toDate),
            default => null,
        };
    }

    private function resolveCustomDateRange(?string $fromDate, ?string $toDate): ?array
    {
        if (!$fromDate || !$toDate) {
            return null;
        }

        return [
            'start_date' => Carbon::parse($fromDate)->startOfDay(),
            'end_date' => Carbon::parse($toDate)->endOfDay(),
        ];
    }
}
