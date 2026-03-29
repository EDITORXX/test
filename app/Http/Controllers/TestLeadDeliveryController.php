<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\FcmToken;
use App\Models\Lead;
use App\Models\PushSubscription;
use App\Models\Task;
use App\Models\TelecallerTask;
use App\Models\User;
use App\Notifications\LeadAssignedNotification;
use App\Services\LeadAssignmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TestLeadDeliveryController extends Controller
{
    public function __construct(
        private readonly LeadAssignmentWorkflowService $leadAssignmentWorkflowService
    ) {
    }

    public function index()
    {
        $users = User::with('role:id,name,slug')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id', 'is_active']);

        $fcmCounts = FcmToken::selectRaw('user_id, COUNT(*) as aggregate')
            ->groupBy('user_id')
            ->pluck('aggregate', 'user_id');

        $pushCounts = PushSubscription::selectRaw('user_id, COUNT(*) as aggregate')
            ->groupBy('user_id')
            ->pluck('aggregate', 'user_id');

        foreach ($users as $user) {
            $user->fcm_tokens_count = (int) ($fcmCounts[$user->id] ?? 0);
            $user->push_subscriptions_count = (int) ($pushCounts[$user->id] ?? 0);
        }

        return view('test.lead-delivery', [
            'users' => $users,
            'result' => session('leadDeliveryResult'),
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'test_note' => ['nullable', 'string', 'max:500'],
        ]);

        $targetUser = User::with('role:id,name,slug')->findOrFail($validated['user_id']);
        abort_unless($targetUser->is_active, 422, 'Selected user is inactive.');

        $actor = $request->user();
        $lead = null;
        $workflow = null;
        $error = null;

        try {
            $lead = $this->createDummyLead($actor->id, $validated['test_note'] ?? null);

            $workflow = $this->leadAssignmentWorkflowService->assignLead(
                $lead,
                $targetUser->id,
                $actor->id,
                $validated['test_note'] ?? 'Notification delivery test',
                true,
                true
            );
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $result = $this->buildResultPayload(
            $actor,
            $targetUser->fresh('role'),
            $lead?->fresh(['activeAssignments.assignedTo', 'creator']),
            $workflow,
            $validated['test_note'] ?? null,
            $error
        );

        return redirect()
            ->route('test.lead-delivery')
            ->withInput()
            ->with($error ? 'error' : 'success', $error ? 'Lead delivery test failed.' : 'Lead delivery test executed.')
            ->with('leadDeliveryResult', $result);
    }

    private function createDummyLead(int $createdBy, ?string $testNote = null): Lead
    {
        $stamp = now()->format('YmdHis');
        $suffix = Str::upper(Str::random(4));
        $phone = '9' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
        $note = trim(collect([
            'TEST-NOTIFY dummy lead for notification delivery diagnostics.',
            $testNote,
        ])->filter()->implode(' '));

        return Lead::create([
            'name' => "TEST-NOTIFY {$stamp}-{$suffix}",
            'phone' => $phone,
            'email' => "test-notify-{$stamp}-" . strtolower($suffix) . '@example.com',
            'status' => 'new',
            'source' => Lead::normalizeSource('other'),
            'notes' => $note,
            'created_by' => $createdBy,
        ]);
    }

    private function buildResultPayload(
        User $actor,
        User $targetUser,
        ?Lead $lead,
        ?array $workflow,
        ?string $testNote,
        ?string $error
    ): array {
        $leadId = $lead?->id;
        $task = $leadId ? $this->resolveTask($leadId, $targetUser) : null;
        $databaseNotification = $leadId ? $this->resolveDatabaseNotification($targetUser, $leadId) : null;
        $appNotification = $leadId ? $this->resolveAppNotification($targetUser, $leadId) : null;
        $fcmCount = FcmToken::where('user_id', $targetUser->id)->count();
        $pushCount = PushSubscription::where('user_id', $targetUser->id)->count();
        $actionUrl = data_get($databaseNotification?->data, 'action_url')
            ?: $appNotification?->action_url
            ?: data_get($workflow, 'task_result.action_url');

        return [
            'generated_at' => now()->toDateTimeString(),
            'queue_connection' => config('queue.default'),
            'error' => $error,
            'test_note' => $testNote,
            'actor' => [
                'id' => $actor->id,
                'name' => $actor->name,
            ],
            'lead' => [
                'id' => $leadId,
                'name' => $lead?->name,
                'phone' => $lead?->phone,
                'email' => $lead?->email,
                'created_at' => optional($lead?->created_at)->toDateTimeString(),
                'show_url' => $leadId ? route('leads.show', $leadId) : null,
            ],
            'assignment' => [
                'exists' => (bool) optional($lead?->activeAssignments?->first()),
                'assignment_id' => data_get($workflow, 'assignment_id'),
                'assigned_to' => $targetUser->name,
                'assigned_to_role' => $targetUser->role->name ?? $targetUser->role->slug ?? 'Unknown',
                'assigned_by' => $actor->name,
                'event_dispatched' => (bool) data_get($workflow, 'event_dispatched', false),
                'old_owner_ids' => data_get($workflow, 'old_owner_ids', []),
                'transferred_counts' => data_get($workflow, 'transferred_counts', []),
            ],
            'task' => [
                'expected_skip' => $targetUser->isAdmin(),
                'skip_reason' => $targetUser->isAdmin() ? 'Admin is excluded from auto task creation by design.' : null,
                'task_type' => data_get($workflow, 'task_result.task_type') ?? data_get($task, 'task_type'),
                'task_id' => data_get($workflow, 'task_result.task_id') ?? data_get($task, 'id'),
                'task_status' => data_get($task, 'status'),
                'task_url' => data_get($workflow, 'task_result.action_url') ?: data_get($task, 'url'),
                'outcome' => $this->resolveTaskOutcome($workflow, $task, $targetUser),
                'task_error' => data_get($workflow, 'task_error'),
            ],
            'notifications' => [
                'database_present' => (bool) $databaseNotification,
                'database_id' => $databaseNotification?->id,
                'database_message' => data_get($databaseNotification?->data, 'message'),
                'database_type' => $databaseNotification?->type,
                'app_present' => (bool) $appNotification,
                'app_id' => $appNotification?->id,
                'app_title' => $appNotification?->title,
                'app_message' => $appNotification?->message,
                'action_url' => $actionUrl,
            ],
            'push' => [
                'fcm_tokens_count' => $fcmCount,
                'push_subscriptions_count' => $pushCount,
                'status' => $this->resolvePushStatus($targetUser, $databaseNotification, $appNotification, $fcmCount, $pushCount),
            ],
            'log_tail' => $this->tailRelevantLogs($leadId, $targetUser->id),
        ];
    }

    private function resolveTask(int $leadId, User $targetUser): ?array
    {
        $telecallerTask = TelecallerTask::where('lead_id', $leadId)
            ->where('assigned_to', $targetUser->id)
            ->latest('id')
            ->first();

        if ($telecallerTask) {
            return [
                'id' => $telecallerTask->id,
                'status' => $telecallerTask->status,
                'task_type' => 'telecaller_task',
                'url' => route('telecaller.tasks') . '?status=pending&task_id=' . $telecallerTask->id,
            ];
        }

        $task = Task::where('lead_id', $leadId)
            ->where('assigned_to', $targetUser->id)
            ->where('type', 'phone_call')
            ->latest('id')
            ->first();

        if (!$task) {
            return null;
        }

        return [
            'id' => $task->id,
            'status' => $task->status,
            'task_type' => 'task',
            'url' => url('/tasks?status=pending&task_id=' . $task->id),
        ];
    }

    private function resolveDatabaseNotification(User $targetUser, int $leadId): ?DatabaseNotification
    {
        return $targetUser->notifications()
            ->where('type', LeadAssignedNotification::class)
            ->latest()
            ->limit(20)
            ->get()
            ->first(function (DatabaseNotification $notification) use ($leadId) {
                return (int) data_get($notification->data, 'lead_id') === $leadId;
            });
    }

    private function resolveAppNotification(User $targetUser, int $leadId): ?AppNotification
    {
        return AppNotification::where('user_id', $targetUser->id)
            ->where('type', AppNotification::TYPE_NEW_LEAD)
            ->latest()
            ->limit(20)
            ->get()
            ->first(function (AppNotification $notification) use ($leadId) {
                return (int) data_get($notification->data, 'lead_id') === $leadId;
            });
    }

    private function resolveTaskOutcome(?array $workflow, ?array $task, User $targetUser): string
    {
        if ($targetUser->isAdmin()) {
            return 'skipped_for_admin';
        }

        if (data_get($workflow, 'task_result.created')) {
            return 'created_new';
        }

        if ($task) {
            return 'reused_or_transferred';
        }

        return 'not_found';
    }

    private function resolvePushStatus(
        User $targetUser,
        ?DatabaseNotification $databaseNotification,
        ?AppNotification $appNotification,
        int $fcmCount,
        int $pushCount
    ): string {
        if ($targetUser->isAdmin()) {
            return 'admin selected, task intentionally skipped';
        }

        if (($databaseNotification || $appNotification) && $fcmCount > 0) {
            return 'ready';
        }

        if (($databaseNotification || $appNotification) && $pushCount > 0) {
            return 'notification saved but lead flow has no FCM token';
        }

        if ($databaseNotification || $appNotification) {
            return 'notification saved but no push target';
        }

        return 'notification not confirmed';
    }

    private function tailRelevantLogs(?int $leadId, int $userId): array
    {
        $path = storage_path('logs/laravel.log');
        if (!File::exists($path)) {
            return [];
        }

        $lines = array_slice(file($path) ?: [], -250);

        $filtered = array_values(array_filter($lines, function ($line) use ($leadId, $userId) {
            $patterns = [
                'LeadAssigned',
                'CreateTelecallerTask',
                'SendLeadAssignedNotification',
                'SendNewLeadNotification',
                'FCM',
                'push',
                'notification',
            ];

            foreach ($patterns as $pattern) {
                if (stripos($line, $pattern) !== false) {
                    return true;
                }
            }

            if ($leadId && str_contains($line, (string) $leadId)) {
                return true;
            }

            return str_contains($line, (string) $userId);
        }));

        return array_slice(array_map('trim', $filtered), -15);
    }
}
