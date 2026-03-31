@extends('layouts.manager')

@section('content')
<style>
  .page-header {
    font-family: "Century Gothic", sans-serif;
    color: #2c3e50;
    font-weight: 700;
    font-size: 2.4rem;
    border-left: 6px solid #138056ff;
    padding-left: 15px;
    margin-bottom: 50px;
    letter-spacing: 0.04em;
  }
  .filter-export-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 50px;
  }
  .filter-export-container form {
    display: flex;
    gap: 10px;
  }
  .filter-export-container input[type="date"] {
    padding: 6px 12px;
    border-radius: 5px;
    border: 2px solid #3994a8ff;
    font-size: 18px;
    color: gray;
  }
  .filter-export-container button {
    background: #138056ff;
    color: white;
    padding: 7px 20px;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .filter-export-container button:hover {
    background: #0f6b4f;
  }
  .stats-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 40px;
  }
  .stat-card {
    flex: 1 1 calc(20% - 20px);
    min-width: 200px;
    background: #fff;
    border-radius: 15px;
      box-shadow: 0 0 10px #a7bd8dff;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transition: box-shadow 0.3s, transform 0.3s;
  }
  .stat-card:hover {
    box-shadow: 0 20px 40px rgba(99, 99, 99, 0.18);
    transform: translateY(-4px);
  }
  .stat-icon {
    font-size: 24px;
    padding: 10px;
    background: #e0f2fe;
    color: #0891b2;
    border-radius: 50%;
    margin-bottom: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }
  .stat-title {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 8px;
    color: #334155;
  }
  .stat-number {
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 4px;
    color: #0f172a;
  }
  .recent-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
  }
  .recent-table th {
    font-weight: 600;
    color: #475569;
    font-size: 1rem;
    padding-bottom: 10px;
    text-align: left;
    border-bottom: 2px solid #e2e8f0;
  }
  .recent-table td {
    background: #f1f5f9;
    padding: 12px 15px;
    border-radius: 12px;
    font-size: 0.95rem;
    color: #334155;
    vertical-align: middle;
  }
  .recent-table tbody tr:hover {
    background-color: #e0e7ff;
    box-shadow: 0 4px 12px rgba(59,130,246,0.2);
  }
  .status-label {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
    display: inline-block;
    min-width: 80px;
    text-align: center;
    text-transform: uppercase;
  }
  .status-pending { background-color: #facc15; }
  .status-overdue { background-color: #d946ef; }
  .status-rejected { background-color: #ef4444; }
  .status-approved { background-color: #22c55e; }
  .status-withdrawn { background-color: #6e4111ff; }
  .status-default { background-color: #6b7280; }
</style>
<div class="container-fluid">
    <h2 class="page-header">Leave Requests Report</h2>

    <div class="filter-export-container">
        <form id="filterForm" method="GET" action="{{ route('manager.reports') }}">
            <input type="date" name="start_date" value="{{ request('start_date') }}">
            <input type="date" name="end_date" value="{{ request('end_date') }}">
            <button type="submit">Filter</button>
            <a href="{{ route('manager.reports') }}" style="display:inline-flex; align-items:center; justify-content:center; background:#f8f9fa; color:#0f172a; padding:7px 20px; font-weight:600; border-radius:6px; border: 1px solid #ccc; text-decoration:none;">Clear</a>
        </form>

        <form method="GET" action="{{ route('manager.reports.export_csv') }}">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
            <button type="submit">Export CSV</button>
        </form>
    </div>

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-list-ul"></i></div>
            <div class="stat-title">Total Requests</div>
            <div class="stat-number">{{ $totalRequests ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#ffe4b5; color:#c77800;"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-title">Pending Requests</div>
            <div class="stat-number">{{ $pendingRequests ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce7f3; color:#9d174d;"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-title">Overdue Requests</div>
            <div class="stat-number">{{ $overdueRequests ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#c6f6d5; color:#276749;"><i class="fas fa-check-circle"></i></div>
            <div class="stat-title">Approved Requests</div>
            <div class="stat-number">{{ $approvedRequests ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fecaca; color:#b91c1c;"><i class="fas fa-ban"></i></div>
            <div class="stat-title">Rejected Requests</div>
            <div class="stat-number">{{ $rejectedRequests ?? 0 }}</div>
        </div>
    </div>

    <h3 style="margin-top:40px; margin-bottom:20px; color:#2c3e50;">Recent Leave Requests</h3>
    <table class="recent-table" role="table" aria-label="Recent Leave Requests">
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Type</th>
                <th>Duration</th>
                <th>Days</th>
                <th>Status</th>
                <th>Requested At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentLeaves as $leave)
            <tr>
                <td>{{ $leave->user->first_name ?? 'Unknown' }}</td>
                <td>{{ $leave->leave_type }}</td>
                <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                <td>{{ $leave->days }}</td>
                @php
                    $isOverdue = method_exists($leave, 'isOverdue') ? $leave->isOverdue() : false;
                    $statusText = $isOverdue ? 'overdue' : strtolower($leave->status);
                    $statusClass = match ($statusText) {
                        'pending' => 'status-pending',
                        'overdue' => 'status-overdue',
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                        'withdrawn' => 'status-withdrawn',
                        default => 'status-default',
                    };
                @endphp
                <td><div class="status-label {{ $statusClass }}">{{ $statusText === 'overdue' ? 'OVERDUE' : strtoupper($leave->status) }}</div></td>
                <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('d M Y H:i') }}</td>
            </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">No recent requests</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('filterForm').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;
        Swal.fire({
            icon: 'success',
            title: 'Filter Successful',
            text: 'Filtering was successful. You can now proceed to export the CSV.',
            confirmButtonText: 'OK'
        }).then(result => {
            if(result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endsection
