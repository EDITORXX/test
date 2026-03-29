<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMeetingReminders extends Command
{
    protected $signature = 'notifications:meeting-reminders';

    protected $description = 'Send meeting reminder notifications to assigned users';

    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->addMinutes(5)->startOfMinute();
        $windowEnd = $windowStart->copy()->endOfMinute();

        $meetings = Meeting::whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->where('status', 'scheduled')
            ->whereNull('completed_at')
            ->whereNull('reminder_sent_at')
            ->with(['lead', 'assignedTo'])
            ->get();

        $notifiedCount = 0;

        foreach ($meetings as $meeting) {
            try {
                $notifications = $this->notificationService->notifyMeetingReminder($meeting);
                if ($notifications->isNotEmpty()) {
                    $meeting->forceFill(['reminder_sent_at' => $now])->save();
                    $notifiedCount += $notifications->count();
                }
            } catch (\Exception $e) {
                $this->error("Failed to send notification for meeting {$meeting->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$notifiedCount} meeting reminder notification(s).");

        return self::SUCCESS;
    }
}
