<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotifyOverdueLeaveRequestsCommand extends Command
{
    protected $signature = 'leave:notify-overdue
                            {--dry-run : Report eligible requests without sending notifications}';

    protected $description = 'Notify applicants, managers, and Senior Managers when leave requests become overdue';

    public function handle(): int
    {
        $query = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->whereNull('overdue_notified_at');

        if ($this->option('dry-run')) {
            $this->info($query->count().' overdue leave request(s) require notification.');

            return self::SUCCESS;
        }

        $notified = 0;
        $failed = 0;

        $query->orderBy('id')->pluck('id')->each(function (int $id) use (&$notified, &$failed): void {
            try {
                $sent = DB::transaction(function () use ($id): bool {
                    $leave = LeaveRequest::query()
                        ->with('user')
                        ->lockForUpdate()
                        ->find($id);

                    if (! $leave || ! $leave->isOverdue() || $leave->overdue_notified_at !== null) {
                        return false;
                    }

                    $recipients = collect();
                    if ($leave->user) {
                        $recipients->push($leave->user);

                        if ($leave->user->manager_id) {
                            $manager = User::query()
                                ->managers()
                                ->whereKey($leave->user->manager_id)
                                ->where('is_active', true)
                                ->first();
                            if ($manager) {
                                $recipients->push($manager);
                            }
                        }
                    }

                    $recipients = $recipients
                        ->merge(User::query()->where('role', 'admin')->where('is_active', true)->get())
                        ->unique('id')
                        ->values();

                    Notification::send($recipients, new LeaveRequestOverdueNotification($leave));

                    $leave->overdue_notified_at = Carbon::now();
                    $leave->save();

                    return true;
                });

                if ($sent) {
                    $notified++;
                }
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
        });

        $this->info("Notified all three parties for {$notified} overdue leave request(s).");
        if ($failed > 0) {
            $this->error("Failed to process {$failed} overdue leave request(s).");
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
