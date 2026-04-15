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
        $this->workingDays = $this->calculateWorkingDays($leaveRequest->start_date, $leaveRequest->end_date);
    }

    private function calculateWorkingDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $publicHolidays = [];
        for ($year = $start->year; $year <= $end->year; $year++) {
            $holidays = Yasumi::create('SouthAfrica', $year);
            foreach ($holidays as $holiday) {
                $publicHolidays[] = $holiday->format('Y-m-d');
            }
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
