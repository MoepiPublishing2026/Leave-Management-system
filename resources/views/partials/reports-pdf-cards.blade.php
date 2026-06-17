<table class="cards">
    <tr>
        <td style="background:#00C0A3;color:#fff;border:1px solid #00C0A3;text-align:center;">
            <div class="k" style="color:#fff;">All Requests</div>
            <div class="v" style="color:#fff;">{{ $totalRequests ?? 0 }}</div>
        </td>
        <td style="background:#C291A4;color:#fff;border:1px solid #C291A4;text-align:center;">
            <div class="k" style="color:#fff;">Pending Requests</div>
            <div class="v" style="color:#fff;">{{ $pendingRequests ?? 0 }}</div>
        </td>
        <td style="background:#C154C1;color:#fff;border:1px solid #C154C1;text-align:center;">
            <div class="k" style="color:#fff;">Overdue Requests</div>
            <div class="v" style="color:#fff;">{{ $overdueRequests ?? 0 }}</div>
        </td>
        <td style="background:#E27A53;color:#fff;border:1px solid #E27A53;text-align:center;">
            <div class="k" style="color:#fff;">Approved Requests</div>
            <div class="v" style="color:#fff;">{{ $approvedRequests ?? 0 }}</div>
        </td>
        <td style="background:#87AFC7;color:#fff;border:1px solid #87AFC7;text-align:center;">
            <div class="k" style="color:#fff;">Rejected Requests</div>
            <div class="v" style="color:#fff;">{{ $rejectedRequests ?? 0 }}</div>
        </td>
    </tr>
</table>
