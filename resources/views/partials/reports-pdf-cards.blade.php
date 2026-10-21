@php
    $cardAll = '#d2c0fa';
    $cardPending = '#bad550';
    $cardOverdue = '#9cacf4';
    $cardApproved = '#25c280';
    $cardRejected = '#f3b86b';
@endphp
<table class="pdf-cards" cellpadding="0" cellspacing="8">
    <tr>
        <td class="pdf-card" style="background-color: {{ $cardAll }};">
            <div class="pdf-card-title">All Requests</div>
            <div class="pdf-card-value">{{ $totalRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardPending }};">
            <div class="pdf-card-title">Pending Requests</div>
            <div class="pdf-card-value">{{ $pendingRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardOverdue }};">
            <div class="pdf-card-title">Overdue Requests</div>
            <div class="pdf-card-value">{{ $overdueRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardApproved }};">
            <div class="pdf-card-title">Approved Requests</div>
            <div class="pdf-card-value">{{ $approvedRequests ?? 0 }}</div>
        </td>
        <td class="pdf-card" style="background-color: {{ $cardRejected }};">
            <div class="pdf-card-title">Rejected Requests</div>
            <div class="pdf-card-value">{{ $rejectedRequests ?? 0 }}</div>
        </td>
    </tr>
</table>
