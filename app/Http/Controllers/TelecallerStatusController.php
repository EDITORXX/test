<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\TelecallerStatusService;
use App\Services\UserStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TelecallerStatusController extends Controller
{
    protected $statusService;
    protected $userStatusService;

    public function __construct(
        TelecallerStatusService $statusService,
        UserStatusService $userStatusService
    ) {
        $this->statusService = $statusService;
        $this->userStatusService = $userStatusService;
    }

    /**
     * Index - show all user statuses (except admin)
     */
    public function index()
    {
        $statusFilter = request('status', 'all');
        $eligibleRoleIds = Role::whereIn('slug', [
            Role::SALES_EXECUTIVE,
            Role::SALES_MANAGER,
            Role::ASSISTANT_SALES_MANAGER,
        ])->pluck('id');

        $users = User::whereIn('role_id', $eligibleRoleIds)
            ->where('is_active', true)
            ->with(['role', 'userProfile', 'telecallerProfile', 'telecallerDailyLimit'])
            ->get()
            ->map(function ($user) {
                $userProfile = $user->userProfile;
                $telecallerProfile = $user->telecallerProfile;
                $isAbsent = $userProfile ? $userProfile->isCurrentlyAbsent() : false;
                $hasScheduledLeadOff = $userProfile ? $userProfile->hasUpcomingLeadOffWindow() : false;
                $leadOffStartAt = $userProfile?->lead_off_start_at;
                $leadOffEndAt = $userProfile?->lead_off_end_at ?? $userProfile?->absent_until;
                
                // For telecallers, use TelecallerStatusService for detailed checks
                // For other users, use UserStatusService for absent check only
                if ($user->isSalesExecutive()) {
                    $canReceive = $this->statusService->canReceiveAssignment($user->id);
                    $pendingCount = $this->statusService->getPendingLeadsCount($user->id);
                    $maxPendingLeads = $telecallerProfile?->max_pending_leads ?? 50;
                } else {
                    $canReceive = [
                        'can_receive' => $this->userStatusService->canUserReceiveLeads($user->id),
                        'is_absent' => $isAbsent,
                        'has_reached_threshold' => false,
                        'pending_count' => 0,
                        'max_pending' => null,
                    ];
                    $pendingCount = 0;
                    $maxPendingLeads = null;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? '',
                    'is_sales_executive' => $user->isSalesExecutive(),
                    'is_absent' => $isAbsent,
                    'has_scheduled_lead_off' => $hasScheduledLeadOff,
                    'absent_reason' => $userProfile?->absent_reason,
                    'absent_until' => $leadOffEndAt,
                    'lead_off_start_at' => $leadOffStartAt,
                    'lead_off_end_at' => $leadOffEndAt,
                    'lead_off_source' => $userProfile?->lead_off_source ?? ($userProfile?->is_absent ? 'crm' : null),
                    'pending_count' => $pendingCount,
                    'max_pending_leads' => $maxPendingLeads,
                    'can_receive' => $canReceive['can_receive'],
                    'active_assigned_count' => $user->assignedLeads()->where('is_active', true)->count(),
                    'returns_today' => $userProfile?->returnsToday() ?? false,
                ];
            })
            ->filter(function ($user) use ($statusFilter) {
                return match ($statusFilter) {
                    'off' => $user['is_absent'],
                    'scheduled' => $user['has_scheduled_lead_off'],
                    'on' => !$user['is_absent'],
                    'returning_today' => $user['returns_today'],
                    default => true,
                };
            })
            ->values();

        $summary = [
            'total_users' => $users->count(),
            'lead_off_users' => $users->where('is_absent', true)->count(),
            'lead_on_users' => $users->where('is_absent', false)->count(),
            'returning_today' => $users->where('returns_today', true)->count(),
            'scheduled_windows' => $users->where('has_scheduled_lead_off', true)->count(),
        ];

        return view('lead-assignment.telecaller-status', compact('users', 'summary', 'statusFilter'));
    }

    /**
     * Update telecaller status
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'is_absent' => 'required|boolean',
            'absent_reason' => 'nullable|string|max:500',
            'mode' => 'nullable|in:normal,now,schedule,on',
            'absent_until' => 'nullable|date',
            'lead_off_start_at' => 'nullable|date',
            'lead_off_end_at' => 'nullable|date',
        ]);

        $validator->after(function ($validator) use ($request) {
            $isAbsent = $request->boolean('is_absent');
            $mode = $request->input('mode');
            $leadOffStartAt = $request->filled('lead_off_start_at') ? Carbon::parse($request->input('lead_off_start_at')) : null;
            $leadOffEndAt = $request->filled('lead_off_end_at') ? Carbon::parse($request->input('lead_off_end_at')) : null;
            $absentUntil = $request->filled('absent_until') ? Carbon::parse($request->input('absent_until')) : null;
            $now = now();

            if (!$isAbsent || $mode === 'on') {
                return;
            }

            if ($mode === 'schedule') {
                if (!$leadOffStartAt) {
                    $validator->errors()->add('lead_off_start_at', 'Lead Off From is required for a scheduled window.');
                }

                if (!$leadOffEndAt) {
                    $validator->errors()->add('lead_off_end_at', 'Lead Off Until is required for a scheduled window.');
                }

                if ($leadOffStartAt && $leadOffStartAt->lte($now)) {
                    $validator->errors()->add('lead_off_start_at', 'Lead Off From must be a future time for a scheduled window.');
                }

                if ($leadOffStartAt && $leadOffEndAt && $leadOffEndAt->lte($leadOffStartAt)) {
                    $validator->errors()->add('lead_off_end_at', 'Scheduled end time must be after start time.');
                }

                return;
            }

            $effectiveEndAt = $leadOffEndAt ?? $absentUntil;
            if ($effectiveEndAt && $effectiveEndAt->lte($now)) {
                $validator->errors()->add('absent_until', 'Lead Off Until must be after now.');
            }
        });

        if ($validator->fails()) {
            $message = collect($validator->errors()->all())->first() ?: 'Please correct the highlighted errors.';
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        
        // Check if user is admin (admins cannot be marked absent)
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin users cannot be marked as absent.'
            ], 422);
        }

        $leadOffStartAt = $request->lead_off_start_at ? Carbon::parse($request->lead_off_start_at) : null;
        $leadOffEndAt = $request->lead_off_end_at ? Carbon::parse($request->lead_off_end_at) : null;
        $absentUntil = $request->absent_until ? Carbon::parse($request->absent_until) : $leadOffEndAt;

        if ($request->boolean('is_absent') && !$leadOffStartAt) {
            $leadOffStartAt = now();
        }

        // Use UserStatusService for all users (not just telecallers)
        $profile = $this->userStatusService->toggleAbsentStatus(
            $user->id,
            $request->is_absent,
            $request->absent_reason,
            $absentUntil,
            $leadOffStartAt,
            $leadOffEndAt,
            'crm',
            optional($request->user())->id
        );

        return response()->json([
            'success' => true,
            'message' => $request->boolean('is_absent')
                ? 'Lead allocation updated successfully.'
                : 'Lead allocation enabled successfully.',
            'profile' => $profile->fresh(),
        ]);
    }

    /**
     * Get user status (API)
     */
    public function getStatus(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->input('telecaller_id'); // Support both for backward compatibility
        
        if (!$userId) {
            return response()->json(['error' => 'User ID required'], 422);
        }

        $user = User::findOrFail($userId);
        $userProfile = $this->userStatusService->getOrCreateProfile($user->id);
        
        if ($user->isTelecaller()) {
            $canReceive = $this->statusService->canReceiveAssignment($user->id);
            $pendingCount = $this->statusService->getPendingLeadsCount($user->id);
            $telecallerProfile = $this->statusService->getOrCreateProfile($user->id);
        } else {
            $canReceive = [
                'can_receive' => $this->userStatusService->canUserReceiveLeads($user->id),
                'is_absent' => $userProfile->isCurrentlyAbsent(),
                'has_reached_threshold' => false,
                'pending_count' => 0,
                'max_pending' => null,
            ];
            $pendingCount = 0;
            $telecallerProfile = null;
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'is_absent' => $userProfile->isCurrentlyAbsent(),
            'lead_off_enabled' => (bool) $userProfile->is_absent,
            'has_scheduled_lead_off' => $userProfile->hasUpcomingLeadOffWindow(),
            'absent_reason' => $userProfile->absent_reason,
            'absent_until' => $userProfile->lead_off_end_at ?? $userProfile->absent_until,
            'lead_off_start_at' => $userProfile->lead_off_start_at,
            'lead_off_end_at' => $userProfile->lead_off_end_at ?? $userProfile->absent_until,
            'lead_off_source' => $userProfile->lead_off_source,
            'pending_count' => $pendingCount,
            'max_pending_leads' => $telecallerProfile?->max_pending_leads ?? null,
            'can_receive' => $canReceive['can_receive'],
        ]);
    }
}
