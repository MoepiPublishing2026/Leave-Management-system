@extends('layouts.app')

@section('title', 'My Leave Requests')

@section('content')
<style>
  .leave-container {
    max-width: 1500px;
    margin: 30px auto;
    background: #fff;
    border-radius: 18px;
      box-shadow: 0 0 10px #a7bd8dff;
    padding: 32px 36px;
  }
  .leave-header-flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
  }
  .leave-title {
    font-weight: 700;
    font-size: 36px;
    color: #2c3e50;
    margin: 0 0 24px 0;
    border-left: 6px solid #138056ff;
    padding-left: 15px;
    letter-spacing: 0.04em;
  }
  .leave-desc {
    color: #7c8299;
    font-size: 1.05rem;
  }
  .leave-btn {
    background: #b2cd16;
    color: white;
    font-weight: 700;
    font-size: 15px;
    padding: 11px 26px;
    border-radius: 7px;
    border: none;
    transition: background 0.25s;
    text-decoration: none;
    display: inline-block;
  }
  .leave-btn:hover {
    background: #1d1e1fff;
  }
  .leave-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
    border: 1px solid #ddd;
    font-size: 1rem;
  }
  .leave-table th, .leave-table td {
    padding: 15px 20px;
    text-align: center;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    font-weight: 500;
    color: #333;
  }
  .leave-table th {
    background-color: #f9fafb;
    color: #616161;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
  }
  .leave-table tbody tr:hover {
    background-color: #f1f5f9;
  }
  .leave-type {
    font-weight: 600;
    display: flex;
    justify-content: center;
    gap: 7px;
    color: #222;
  }
  .badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 13px;
    vertical-align: middle;
    min-width: 90px;
    text-transform: uppercase;
  }
  .badge-pending {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fbbf24;
  }
  .badge-approved {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #22c55e;
  }
  .badge-rejected {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
  }
  .badge-withdrawn {
    background-color: #fef3c7;
    color: #78350f;
    border: 1px solid #fbbf24;
  }
  .badge-overdue {
    background-color: #fce7f3;
    color: #9d174d;
    border: 1px solid #f472b6;
  }
  .leave-action {
    color: #ef4444;
    background: none;
    border: none;
    font-weight: 700;
    cursor: pointer;
    text-decoration: underline;
    transition: color 0.2s;
    padding: 0;
  }
  .leave-action:hover {
    color: #b12a2a;
  }
  .na-action {
    color: #a0a6b1;
    font-style: italic;
  }
  .no-requests {
    text-align: center;
    padding: 30px;
    color: #a0a6b1;
    font-style: italic;
    font-size: 1.1rem;
  }
</style>

<div class="leave-container">
  <div class="leave-header-flex">
    <div>
      <div class="leave-desc">View and manage your leave applications</div>
    </div>
    <a href="{{ route('apply') }}" class="leave-btn">+ New Request</a>
  </div>

  <div class="filter-links" style="margin-top: 10px; margin-bottom: 16px;">
    <a href="{{ route('leave.myRequests') }}" class="active" style="margin-right: 12px; text-decoration: underline; color: #0a382b; font-weight: 800;">All</a>
    <a href="{{ route('leave.status', ['status' => 'pending']) }}" style="margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800;">Pending</a>
    <a href="{{ route('leave.status', ['status' => 'overdue']) }}" style="margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800;">Overdue</a>
    <a href="{{ route('leave.status', ['status' => 'approved']) }}" style="margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800;">Approved</a>
    <a href="{{ route('leave.status', ['status' => 'rejected']) }}" style="margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800;">Rejected</a>
    <a href="{{ route('leave.status', ['status' => 'withdrawn']) }}" style="margin-right: 12px; text-decoration: none; color: #138056ff; font-weight: 800;">Withdrawn</a>
  </div>

  @if ($leaveRequests->isNotEmpty())
  <table class="leave-table" role="table" aria-label="My Leave Requests">
    <thead>
      <tr>
        <th scope="col">Type</th>
        <th scope="col">Dates</th>
        <th scope="col">Days</th>
        <th scope="col">Status</th>
        <th scope="col">Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($leaveRequests as $request)
      <tr>
        <td class="leave-type">{{ $request->leave_type }}</td>
        <td>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</td>
        <td>{{ $request->days }}</td>
        <td>
          @php $status = strtolower(trim($request->status)); @endphp
          @if ($request->isOverdue())
            <span class="badge badge-overdue" role="status" aria-label="Overdue">Overdue</span>
          @elseif ($status === 'pending')
            <span class="badge badge-pending" role="status" aria-label="Pending">Pending</span>
          @elseif ($status === 'approved')
            <span class="badge badge-approved" role="status" aria-label="Approved">Approved</span>
          @elseif ($status === 'rejected')
            <span class="badge badge-rejected" role="status" aria-label="Rejected">Rejected</span>
          @elseif ($status === 'withdrawn')
            <span class="badge badge-withdrawn" role="status" aria-label="Withdrawn">Withdrawn</span>
          @else
            <span class="badge" role="status" aria-label="Unknown">{{ $request->status }}</span>
          @endif
        </td>
        <td>
          @if ($status === 'pending')
          <form class="withdraw-form" action="{{ route('leave.withdraw', $request->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('PATCH')
            <button type="submit" class="leave-action" aria-label="Withdraw Leave Request">Withdraw</button>
          </form>
          @else
          <span class="na-action" aria-label="No action available">N/A</span>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="no-requests" role="alert">You have no leave requests yet.</div>
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  document.querySelectorAll('.withdraw-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to withdraw this leave request?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, withdraw it!'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>
@endsection
