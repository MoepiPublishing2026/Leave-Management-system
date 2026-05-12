<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\LeaveRequest;

use Carbon\Carbon;
use Yasumi\Yasumi;

class LeaveRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $workingDays;

    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
        $this->workingDays = $this->calculateWorkingDays($leaveRequest);
    }

    private function calculateWorkingDays(LeaveRequest $leaveRequest)
    {
        $start = Carbon::parse($leaveRequest->start_date);
        $end = Carbon::parse($leaveRequest->end_date);

        $publicHolidays = [];
        for ($year = $start->year; $year <= $end->year; $year++) {
            $holidays = Yasumi::create('SouthAfrica', $year);
            foreach ($holidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
        }

        // Half-day requests should count as 0.5 (if the day is a working day).
        if (!empty($leaveRequest->is_half_day)) {
            if (!$start->isWeekend() && !in_array($start->toDateString(), $publicHolidays)) {
                return 0.5;
            }
            return 0;
        }

        $workingDays = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend() && !in_array($current->toDateString(), $publicHolidays)) {
                $workingDays++;
            }
            $current->addDay();
        }

        return $workingDays;
    }

    public function build()
    {
        return $this->subject('New Leave Request Submitted')
                    ->view('emails.leave_request_submitted');
    }
}
