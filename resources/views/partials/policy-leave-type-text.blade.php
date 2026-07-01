@php
    $policy = $policy ?? null;
    $type = $type ?? '';
@endphp

@if ($policy)
    @if($type === 'Annual')
        Annual leave is the primary leave balance for the year. Approved leave that counts against your annual pool reduces this balance. Sick, Maternity, Paternity, Bereavement, and Unpaid leave do not reduce your annual balance. Sick, Maternity, Paternity, and Bereavement have their own allocations. Once the annual balance reaches 0, other leave types are disabled, but Sick, Bereavement, Maternity, Paternity, and Unpaid can still be requested (with proof where required).
        @if((int) $policy->max_carry_over > 0)
            Up to {{ (int) $policy->max_carry_over }} unused day(s) may be carried over to the next year.
        @endif
    @elseif($type === 'Sick')
        Sick leave is granted up to {{ (int) $policy->default_days }} days for illness or injury. Employees must notify their manager as early as possible. Your manager may approve sick leave before you visit the doctor. Once approved, you must upload your doctor's letter from My Leave Requests on your leave start date, or by the next day at the latest, to confirm your hospital or doctor visit.
    @elseif(in_array($type, ['Maternity', 'Paternity']))
        {{ $type }} leave of {{ (int) $policy->default_days }} days is provided according to applicable legislation. Proper documentation and notice are required.
    @elseif($type === 'Bereavement')
        Employees may take up to {{ (int) $policy->default_days }} days of paid bereavement leave for the death of immediate family members.
    @elseif($type === 'Unpaid')
        Unpaid leave does not reduce your annual leave balance. Employees may request unpaid leave for personal reasons. Approval is at the discretion of management and based on operational requirements.
    @elseif($type === 'Study')
        Study leave may be requested for approved training or educational programs. Supporting documentation is required, and approval is subject to operational requirements.
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
