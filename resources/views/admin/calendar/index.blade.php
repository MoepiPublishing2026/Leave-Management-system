@extends('layouts.admin')

@section('content')

<style>
    body {
        font-family: "Century Gothic", sans-serif;
        background-color: white;
        color: #333;
    }
    h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0px;
        border-left: 6px solid #138056ff;
        padding-left: 3px;
        letter-spacing: 0.04em;
        text-align: left;
    }
    h5 {
        font-weight: 700;
        font-size: 20px;
        color: #5c0646ff;
        margin-bottom: 0;
        padding-left: 0;
        letter-spacing: 0.04em;
        text-align: center;
    }
    .page-header {
        margin-bottom: 20px;
    }
    .calendar-nav {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .calendar-nav a {
        background: #f8f9fa;
        color: black;
        padding: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        user-select: none;
    }
    .calendar-nav a:hover {
        background-color: #e1f5edff;
    }
    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-top: 10px;
        max-width: 1500px;
        margin-left: auto;
        margin-right: auto;
    }
    .day {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        height: 100px;
        background: white;
        cursor: pointer;
        transition: background 0.2s ease-in-out;
        user-select: none;
        position: relative;
        font-size: 0.87rem;
    }
    .day.has-leave {
        background: white !important;
        border: 2px solid #94ee1fff;
    }
    .leave-count {
        position: absolute;
        bottom: 5px;
        right: 8px;
        font-weight: 600;
        color: #444444ff;
        font-size: 0.85rem;
        background: #94ee1fff;
        border-radius: 12px;
        padding: 2px 8px;
        user-select: none;
    }
    .day.day-disabled {
        background: #ffffff;
        color: #9aa0a6;
        cursor: not-allowed;
        opacity: 0.8;
    }
    .day.day-disabled:hover,
    .day.day-disabled:focus {
        background: #ffffff;
    }
    .day:hover,
    .day:focus {
        background-color: #f1f3f5;
        outline: none;
    }
    .day-header {
        font-weight: bold;
        text-align: center;
        background-color: white;
        padding: 8px;
        border-radius: 4px;
        user-select: none;
    }
    ul.leave-details-list {
        padding-left: 20px;
    }
</style>
<h1>Team Leave Calendar</h1>
<div class="container mt-4">
    <div class="calendar-nav">
        <a href="{{ route('admin.calendar', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}">&lt; Prev</a>
        <h5>{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</h5>
        <a href="{{ route('admin.calendar', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}">Next &gt;</a>
    </div>

    <div class="calendar" role="grid" aria-label="Team Leave Calendar">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
            <div class="day-header" role="columnheader">{{ $day }}</div>
        @endforeach

        @for ($i = 0; $i < $startDay; $i++)
            <div aria-hidden="true"></div>
        @endfor

        @for ($day = 1; $day <= $daysInMonth; $day++)
            @php
                $carbonDate = \Carbon\Carbon::create($year, $month, $day);
                $date = $carbonDate->toDateString();
                $isDisabled = $carbonDate->isWeekend() || in_array($date, $publicHolidays);
                $leaveTypes = $events[$date] ?? [];
                $hasLeave = count($leaveTypes) > 0;
            @endphp
            <div
                class="day {{ $hasLeave ? 'has-leave' : '' }} {{ $isDisabled ? 'day-disabled' : '' }}"
                role="gridcell"
                tabindex="{{ $isDisabled ? '-1' : '0' }}"
                @if(!$isDisabled)
                    onclick="showDetails('{{ $date }}')"
                    onkeypress="if(event.key==='Enter'){showDetails('{{ $date }}');}"
                @else
                    aria-disabled="true"
                @endif
            >
                <strong>{{ $day }}</strong>
                @if($hasLeave)
                    <span class="leave-count">{{ count($leaveTypes) . ' on leave' }}</span>
                @endif
            </div>
        @endfor
    </div>
</div>

<!-- Bootstrap modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="leaveModalLabel">People on Leave</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="leaveDetails">
        <p>Select a date to view leave details.</p>
      </div>
    </div>
  </div>
</div>

<script>
    function showDetails(date) {
        const events = @json($events);
        let details = '';

        if (events[date]) {
            details = '<ul class="leave-details-list">';
            events[date].forEach(e => {
                details += `<li><strong>${e.user}</strong> - ${e.type} Leave</li>`;
            });
            details += '</ul>';
        } else {
            details = '<p>No leaves on this day.</p>';
        }

        document.getElementById('leaveDetails').innerHTML = details;

        const modal = new bootstrap.Modal(document.getElementById('leaveModal'));
        modal.show();
    }
</script>

@endsection
