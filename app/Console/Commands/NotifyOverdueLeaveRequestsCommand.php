<?php

namespace App\Console\Commands;

use App\Support\OverdueLeaveNotifier;
use Illuminate\Console\Command;

class NotifyOverdueLeaveRequestsCommand extends Command
{
    protected $signature = 'leave:notify-overdue
                            {--dry-run : Report eligible requests without sending notifications}';

    protected $description = 'Remind managers and seniors about pending leave nearing the start date, and notify when requests become overdue';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $count = \App\Models\LeaveRequest::query()
                ->overdue()
                ->whereNull('overdue_notified_at')
                ->count();
            $this->info($count.' overdue leave request(s) require notification.');

            return self::SUCCESS;
        }

        $notified = OverdueLeaveNotifier::notifyDue();
        $this->info("Notified applicant, manager, and senior manager for {$notified} overdue leave request(s).");
        $this->info('Also sent start-date reminders for pending requests that are nearing the requested date.');

        return self::SUCCESS;
    }
}
