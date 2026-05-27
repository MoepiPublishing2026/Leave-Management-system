<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title ?? 'Leave Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        .header { margin-bottom: 14px; }
        .title { font-size: 18px; font-weight: 700; margin: 0; }
        .subtitle { margin: 4px 0 0; color: #475569; }
        .meta { margin-top: 6px; color: #64748b; font-size: 11px; }
        .cards { margin: 14px 0 12px; width: 100%; border-collapse: collapse; }
        .cards td { padding: 8px 10px; border: 1px solid #e2e8f0; }
        .k { color: #64748b; font-size: 13px; font-weight: 700; letter-spacing: 0.02em; }
        .v { font-weight: 800; font-size: 18px; line-height: 1.05; color: #111827; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f1f5f9; text-align: left; padding: 8px; border: 1px solid #e2e8f0; }
        table.data td { padding: 7px 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $title ?? 'Leave Requests Report' }}</p>
        <p class="subtitle">{{ $subtitle ?? '' }}</p>
        <p class="meta">Generated: {{ optional($generatedAt)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
    </div>

    <table class="cards">
        <tr>
            <td><div class="k">Total</div><div class="v">{{ $totalRequests ?? 0 }}</div></td>
            <td><div class="k">Pending</div><div class="v">{{ $pendingRequests ?? 0 }}</div></td>
            <td><div class="k">Overdue</div><div class="v">{{ $overdueRequests ?? 0 }}</div></td>
            <td><div class="k">Approved</div><div class="v">{{ $approvedRequests ?? 0 }}</div></td>
            <td><div class="k">Rejected</div><div class="v">{{ $rejectedRequests ?? 0 }}</div></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 16%;">Employee</th>
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
                <td>{{ trim(($leave->user->first_name ?? '') . ' ' . ($leave->user->last_name ?? '')) ?: ($leave->user->email ?? 'Unknown') }}</td>
                <td>{{ $leave->leave_type }}</td>
                <td>{{ $leave->start_date }}</td>
                <td>{{ $leave->end_date }}</td>
                <td>{{ !empty($leave->is_half_day) ? 'Half day' : ($leave->days ?? '') }}</td>
                <td>{{ \Illuminate\Support\Str::ucfirst(strtolower(trim((string) $leave->status))) }}</td>
                <td class="muted">{{ optional($leave->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">No records for this filter.</td></tr>
        @endforelse
        </tbody>
    </table>

    <p class="muted" style="margin-top: 16px;">&copy; {{ date('Y') }} Moepi Publishing. All rights reserved.</p>
</body>
</html>

