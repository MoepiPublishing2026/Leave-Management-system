@php
    $halfDaySlot = \App\Support\LeaveWorkdayRules::halfDaySlotForLeave($leaveRequest ?? null);
@endphp
@if ($halfDaySlot !== '')
    @if (!empty($asMetaRow))
        <tr><th>Half-day period</th><td>{{ $halfDaySlot }}</td></tr>
    @elseif (!empty($asInline))
        · {{ $halfDaySlot }}
    @else
        <p style="margin:0 0 16px;">{{ $halfDaySlot }}</p>
    @endif
@endif
