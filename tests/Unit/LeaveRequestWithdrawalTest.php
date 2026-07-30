<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class LeaveRequestWithdrawalTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_approved_leave_can_be_withdrawn_until_its_final_day(): void
    {
        Carbon::setTestNow('2026-07-29 12:00:00');

        $leave = new LeaveRequest([
            'status' => 'approved',
            'start_date' => '2026-07-28',
            'end_date' => '2026-07-29',
        ]);

        $this->assertTrue($leave->canBeWithdrawnByEmployee());
    }

    public function test_leave_cannot_be_withdrawn_after_its_final_day(): void
    {
        Carbon::setTestNow('2026-07-30 00:00:00');

        $leave = new LeaveRequest([
            'status' => 'approved',
            'start_date' => '2026-07-28',
            'end_date' => '2026-07-29',
        ]);

        $this->assertFalse($leave->canBeWithdrawnByEmployee());
    }

    public function test_rejected_leave_cannot_be_withdrawn(): void
    {
        Carbon::setTestNow('2026-07-29 12:00:00');

        $leave = new LeaveRequest([
            'status' => 'rejected',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
        ]);

        $this->assertFalse($leave->canBeWithdrawnByEmployee());
    }
}
