@php
    $policy = $policy ?? null;
    $type = $type ?? '';
@endphp

@if ($policy)
    @if($type === 'Annual')
        Annual leave is a standalone balance for the year. Only approved Annual leave reduces this allocation. Every other leave type (Sick, Maternity, Paternity, Bereavement, Family Responsibility, Study, and Unpaid) uses its own days and does not take from annual leave. Once the annual balance reaches 0, Annual leave cannot be requested; other types can still be requested if they have remaining days (with proof where required).
        @if((int) $policy->max_carry_over > 0)
            Up to {{ (int) $policy->max_carry_over }} unused day(s) may be carried over to the next year.
        @endif
    @elseif($type === 'Sick')
        Sick leave is granted up to {{ (int) $policy->default_days }} days for illness or injury. Employees must notify their manager as early as possible. Your manager may approve sick leave before you visit the doctor. Once approved, you must upload your doctor's letter from My Leave Requests by the day after your leave ends at the latest, to confirm your hospital or doctor visit. If the letter is not submitted by that deadline, the approved request will automatically be changed to unpaid leave.
    @elseif(in_array($type, ['Maternity', 'Paternity']))
        {{ $type }} leave of {{ (int) $policy->default_days }} days is provided according to applicable legislation. Proper documentation and notice are required.
    @elseif($type === 'Bereavement')
        Employees may take up to {{ (int) $policy->default_days }} days of paid bereavement leave for the death of immediate family members.
    @elseif($type === 'Family Responsibility')
        Family responsibility leave of up to {{ (int) $policy->default_days }} days is standalone and does not take days from annual leave. It may be requested when an employee needs to attend to urgent family matters. Approval is subject to operational requirements.
    @elseif($type === 'Unpaid')
        Unpaid leave is a standalone leave type and does not reduce your annual leave balance. Employees may request unpaid leave for personal reasons. Approval is at the discretion of management and based on operational requirements.
    @elseif($type === 'Study')
        Study leave is standalone and does not take days from annual leave. It may be requested for approved training or educational programs. Supporting documentation is required, and approval is subject to operational requirements.
    @else
        {{ $type }} leave details are as per company policy. Employees are allowed {{ (int) $policy->default_days }} days annually.
    @endif
    @if($policy->requires_proof)
        Supporting documentation is required for this leave type.
    @endif
    @if($policy->allow_half_days)
        Half-day requests are allowed.
    @endif
@else
    Policy details are currently unavailable.
@endif
