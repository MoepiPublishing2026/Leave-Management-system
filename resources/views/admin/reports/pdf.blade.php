@extends('layouts.pdf')

@section('title', $title ?? 'Leave Report')

@section('styles')
    @include('partials.reports-pdf-styles')
@endsection

@section('content')
    <div class="header">
        <p class="title">{{ $title ?? 'Leave Requests Report' }}</p>
        <p class="subtitle">{{ $subtitle ?? '' }}</p>
        <p class="meta">Generated: {{ optional($generatedAt)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
    </div>

    @include('partials.reports-pdf-cards')

    <table class="data">
        <thead>
            <tr>
                <th style="width: 16%;">Applicant</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 18%;">Start</th>
                <th style="width: 18%;">End</th>
                <th style="width: 8%;">Days</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 18%;">Requested</th>
            </tr>
        </thead>
        <tbody>
        @forelse(($leaves ?? []) as $leave)
            <tr>
                <td>
                    {{ trim(($leave->user->first_name ?? '') . ' ' . ($leave->user->last_name ?? '')) ?: ($leave->user->email ?? 'Unknown') }}
                    @if (! in_array(strtolower((string) ($leave->user->role ?? '')), ['employee', ''], true))
                        ({{ \App\Support\RoleLabels::display($leave->user->role ?? null) }})
                    @endif
                </td>
                <td>{{ $leave->leave_type }}</td>
                <td>{{ $leave->start_date }}</td>
                <td>{{ $leave->end_date }}</td>
                <td>{{ \App\Support\LeaveDaysFormatter::format($leave->days ?? '') }}</td>
                <td>{{ \App\Support\LeaveStatusLabels::display($leave->status) }}</td>
                <td class="muted">{{ optional($leave->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">No records for this filter.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
