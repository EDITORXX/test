<?php

namespace App\Console\Commands;

use App\Models\SystemSettings;
use Illuminate\Console\Command;

class SecurityLockOff extends Command
{
    protected $signature = 'security-lock:off {--keep-secret : Keep owner bypass secret stored}';

    protected $description = 'Disable security lock for all users.';

    public function handle(): int
    {
        SystemSettings::set('security_lock_all_users', '0');
        SystemSettings::set('security_lock_started_at', '');

        if (!$this->option('keep-secret')) {
            SystemSettings::set('security_lock_owner_secret', '');
        }

        $this->info('Security lock disabled. Users can access the system normally.');

        return self::SUCCESS;
    }
}
