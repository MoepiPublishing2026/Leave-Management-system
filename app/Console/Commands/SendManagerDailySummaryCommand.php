<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\ManagerDailySummaryNotification;
use App\Support\ManagerTeam;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendManagerDailySummaryCommand extends Command
{
    protected $signature = 'leave:send-manager-daily-summary';

    protected $description = 'Optional daily email summary of pending leave requests for each manager';

    public function handle(): int
    {
        $now = Carbon::now();
        $sent = 0;

        $managers = User::query()->managers()->where('is_active', true)->get();

        foreach ($managers as $manager) {
            $teamIds = ManagerTeam::employeeIdsFor($manager);
            if ($teamIds->isEmpty()) {
                continue;
            }

            $pending = LeaveRequest::query()
                ->with('user')
                ->whereIn('user_id', $teamIds)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->orderBy('created_at')
                ->get();

            if ($pending->isEmpty()) {
                continue;
            }

            $overdueCount = 0;
            $items = $pending->map(function (LeaveRequest $leave) use ($now, &$overdueCount) {
                $start = $leave->start_date ? Carbon::parse($leave->start_date)->startOfDay() : null;
                if ($start && $start->lt($now->copy()->startOfDay())) {
                    $overdueCount++;
                }

                $employee = trim(($leave->user->first_name ?? '').' '.($leave->user->last_name ?? '')) ?: 'Employee';

                return (object) [
                    'id' => $leave->id,
                    'employee' => $employee,
                    'leave_type' => $leave->leave_type ?: 'leave',
                    'start_date' => (string) $leave->start_date,
                    'end_date' => (string) $leave->end_date,
                    'hours_pending' => $leave->created_at ? (int) $leave->created_at->diffInHours($now) : 0,
                ];
            });

            try {
                $manager->notify(new ManagerDailySummaryNotification($items, $overdueCount));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->info("Sent {$sent} manager daily summary email(s).");

        return self::SUCCESS;
    }
}
