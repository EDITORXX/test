<?php

namespace App\Services;

use App\Events\LeadAssigned;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Task;
use App\Models\TelecallerTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadAssignmentWorkflowService
{
    public function __construct(
        private readonly LeadOwnerTaskService $leadOwnerTaskService
    ) {
    }

    public function assignLead(
        Lead $lead,
        int $assignedTo,
        int $assignedBy,
        ?string $notes = null,
        bool $createCallingTask = true,
        bool $transferExistingTasks = true
    ): array {
        return DB::transaction(function () use (
            $lead,
            $assignedTo,
            $assignedBy,
            $notes,
            $createCallingTask,
            $transferExistingTasks
        ) {
            $oldAssignments = $lead->assignments()
                ->where('is_active', true)
                ->get(['assigned_to']);

            $oldOwnerIds = $oldAssignments
                ->pluck('assigned_to')
                ->filter(fn ($id) => (int) $id !== (int) $assignedTo)
                ->unique()
                ->values();

            $lead->assignments()->where('is_active', true)->update([
                'is_active' => false,
                'unassigned_at' => now(),
            ]);

            $assignment = LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_to' => $assignedTo,
                'assigned_by' => $assignedBy,
                'assignment_type' => 'primary',
                'notes' => $notes,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $transferredCounts = [
                'telecaller_tasks' => 0,
                'manager_tasks' => 0,
            ];

            if ($transferExistingTasks && $oldOwnerIds->isNotEmpty()) {
                $transferredCounts['telecaller_tasks'] = TelecallerTask::where('lead_id', $lead->id)
                    ->whereIn('assigned_to', $oldOwnerIds)
                    ->whereIn('status', ['pending', 'in_progress', 'rescheduled'])
                    ->update(['assigned_to' => $assignedTo]);

                $transferredCounts['manager_tasks'] = Task::where('lead_id', $lead->id)
                    ->whereIn('assigned_to', $oldOwnerIds)
                    ->where('type', 'phone_call')
                    ->whereIn('status', ['pending', 'in_progress', 'rescheduled'])
                    ->update(['assigned_to' => $assignedTo]);
            }

            $taskResult = [
                'created' => false,
                'task_type' => null,
                'task_id' => null,
                'action_url' => null,
            ];
            $eventDispatched = false;
            $taskError = null;

            if ($createCallingTask) {
                event(new LeadAssigned($lead, $assignedTo, $assignedBy));
                $eventDispatched = true;

                try {
                    $assignee = User::with('role')->find($assignedTo);
                    if ($assignee) {
                        $taskResult = $this->leadOwnerTaskService->ensureOpenTaskForOwner(
                            $lead,
                            $assignee,
                            $assignedBy,
                            $notes
                        );
                    }
                } catch (\Throwable $e) {
                    $taskError = $e->getMessage();

                    Log::warning("LeadAssignmentWorkflowService: fallback task creation failed for lead {$lead->id}: " . $e->getMessage());
                }
            }

            return [
                'assignment_id' => $assignment->id,
                'old_owner_ids' => $oldOwnerIds->all(),
                'transferred_counts' => $transferredCounts,
                'task_result' => $taskResult,
                'task_error' => $taskError,
                'event_dispatched' => $eventDispatched,
            ];
        });
    }
}
