@extends('layouts.app')

@section('title', 'My Leave Calendar')

@section('content')
<style>
    /* Keep layout navbar styling consistent; scope page styles */
    .employee-calendar-page {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: white;
        color: #333;
    }
    .employee-calendar-page h1 {
        font-weight: 700;
        font-size: 40px;
        color: #2c3e50;
        margin: 0px;
        padding-left: 3px;
        letter-spacing: 0.04em;
        text-align: left;
        border-left: 6px solid #138056ff;
    }
    .employee-calendar-page h5 {
        font-weight: 700;
        font-size: 20px;
        color: #5c0646ff;
        margin-bottom: 0;
        padding-left: 0;
        letter-spacing: 0.04em;
        text-align: center;
    }
    .employee-calendar-page .page-header {
        margin-bottom: 20px;
    }
    .employee-calendar-page .calendar-nav {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .employee-calendar-page .calendar-nav a {
        background: #f8f9fa;
        color: black;
        padding: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        user-select: none;
    }
    .employee-calendar-page .calendar-nav a:hover {
        background-color: #e1f5edff;
    }
    .employee-calendar-page .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-top: 10px;
        max-width: 1500px;
        margin-left: auto;
        margin-right: auto;
    }
    .employee-calendar-page .day-header {
        font-weight: bold;
        text-align: center;
        background-color: white;
        padding: 8px;
        border-radius: 4px;
        user-select: none;
    }
    .employee-calendar-page .day {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        height: 100px;
        background: white;
        cursor: default;
        transition: background 0.2s ease-in-out;
        user-select: none;
        position: relative;
        font-size: 0.87rem;
    }
    .day.day-disabled {
        background: #ffffff;
        color: #9aa0a6;
        cursor: not-allowed;
        opacity: 0.8;
    }
    .day.day-disabled:hover {
        background: #ffffff;
    }
    .day:hover {
        background-color: #f1f3f5;
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
    .day.has-leave {
        background: white !important;
        border: 2px solid #94ee1fff;
    }

    /* Modal */
    .modal-header {
        font-weight: 700;
        font-size: 1.2rem;
    }
    ul.leave-details-list {
        padding-left: 20px;
    }
</style>
<div class="employee-calendar-page">
 <h1>My Leave Calendar</h1>


<div class="container mt-4">
   
    <div class="calendar-nav">
        <a href="{{ route('employee.calendar', ['month' => $month == 1 ? 12 : $month - 1, 'year' => $month == 1 ? $year -1 : $year]) }}">&lt; Prev</a>
        <h5>{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</h5>
        <a href="{{ route('employee.calendar', ['month' => $month == 12 ? 1 : $month + 1, 'year' => $month == 12 ? $year +1 : $year]) }}">Next &gt;</a>
    </div>

    <div class="calendar" role="grid" aria-labelledby="calendar-label">
        {{-- Day Headers --}}
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
            <div class="day-header" role="columnheader">{{ $day }}</div>
        @endforeach

        @php
            $firstDay = \Carbon\Carbon::create($year, $month, 1);
            $startDay = $firstDay->dayOfWeek;
            $daysInMonth = $firstDay->daysInMonth;
        @endphp

        {{-- Blank days before first --}}
        @for($i=0; $i<$startDay; $i++)
            <div></div>
        @endfor

        {{-- Days with normalized leave class --}}
        @for($day=1; $day<=$daysInMonth; $day++)
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
                @if(count($leaveTypes) > 0)
                    <span class="leave-count">{{ count($leaveTypes) === 1 ? 'On leave' : (count($leaveTypes) . ' on leave') }}</span>
                @endif
            </div>
        @endfor
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="leaveModalLabel" class="modal-title">Leaves on selected day</h5>
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
            details += '<ul class="leave-details-list">';
            events[date].forEach(e => {
                details += `<li>${e.type}</li>`;
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
</div>
@endsection
