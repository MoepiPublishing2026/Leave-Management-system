<?php

namespace App\Console\Commands;

use App\Support\LeaveReminderService;
use Illuminate\Console\Command;

class SendLeaveRemindersCommand extends Command
{
    protected $signature = 'leave:send-reminders';

    protected $description = 'Send leave-day reminders to employees and managers';

    public function handle(): int
    {
        LeaveReminderService::syncAllApplicants();

        $this->info('Leave reminders processed.');

        return self::SUCCESS;
    }
}
