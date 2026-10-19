@php
    $colors = config('leave_status_colors.colors', []);
    $cardAll = $colors['all'] ?? '#4ee5c5';
    $cardPending = $colors['pending'] ?? '#f3b86b';
    $cardOverdue = $colors['overdue'] ?? '#ae8df5';
    $cardApproved = $colors['approved'] ?? '#a33d86';
    $cardRejected = $colors['rejected'] ?? '#8fc4c7';
@endphp
<table class="pdf-cards" cellpadding="0" cellspacing="8">
    <tr>
        <td class="pdf-card" style="background-color: {{ $cardAll }}; border: 1px solid {{ $cardAll }};">
            <div class="pdf-card-title">All Requests</div>
            <div class="pdf-card-value">{{ $totalRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardPending }}; border: 1px solid {{ $cardPending }};">
            <div class="pdf-card-title">Pending Requests</div>
            <div class="pdf-card-value">{{ $pendingRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardOverdue }}; border: 1px solid {{ $cardOverdue }};">
            <div class="pdf-card-title">Overdue Requests</div>
            <div class="pdf-card-value">{{ $overdueRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardApproved }}; border: 1px solid {{ $cardApproved }};">
            <div class="pdf-card-title">Approved Requests</div>
            <div class="pdf-card-value">{{ $approvedRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardRejected }}; border: 1px solid {{ $cardRejected }};">
            <div class="pdf-card-title">Rejected Requests</div>
            <div class="pdf-card-value">{{ $rejectedRequests ?? 0 }}</div>
        </td>
    </tr>
</table>
