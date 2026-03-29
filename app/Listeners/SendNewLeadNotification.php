<?php

namespace App\Listeners;

use App\Events\LeadAssigned;
use App\Events\DashboardUpdate;
use App\Services\LeadOwnerTaskService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewLeadNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;
    protected $leadOwnerTaskService;

    public function __construct(NotificationService $notificationService, LeadOwnerTaskService $leadOwnerTaskService)
    {
        $this->notificationService = $notificationService;
        $this->leadOwnerTaskService = $leadOwnerTaskService;
    }

    public function handle(LeadAssigned $event): void
    {
        $assignedUser = User::find($event->assignedTo);

        if ($assignedUser) {
            $actionUrl = $this->leadOwnerTaskService->resolveActionUrlForOwner($event->lead, $assignedUser)
                ?? url('/leads/' . $event->lead->id);

            $this->notificationService->notifyNewLead($assignedUser, $event->lead, $actionUrl);

            if ($assignedUser->isSalesExecutive()) {
                event(new DashboardUpdate($assignedUser->id, 'lead_assigned', [
                    'lead_id' => $event->lead->id,
                    'lead_name' => $event->lead->name,
                ]));
            }
        }
    }
}
