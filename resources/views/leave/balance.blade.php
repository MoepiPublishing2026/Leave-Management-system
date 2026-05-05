@extends('layouts.app')

@section('title', 'Leave Balance')

@section('content')
<style>
  .balance-wrap {
    font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(32, 50, 93, 0.08);
    padding: 18px 18px 26px;
  }
  .balance-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
  }
  .balance-header h1 {
    margin: 0;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #2c3e50;
    font-size: 36px;
  }
  .balance-year {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .balance-year label {
    font-weight: 800;
    color: #334155;
    margin: 0;
  }
  .balance-year input {
    width: 120px;
    border-radius: 10px;
    border: 2px solid #a5d817;
    padding: 8px 10px;
    font-weight: 700;
  }
  .grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
  }
  @media (max-width: 980px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }

  .type-card {
    border: 1px solid #e5e7eb;
    border-left: 5px solid #f00e65;
    border-radius: 16px;
    padding: 14px 14px;
    box-shadow: 0 6px 16px rgba(8, 8, 8, 0.06);
    background:rgb(209, 224, 165);
    min-height: 130px;
  }
  .type-card.active {
    border-color: #a5d817;
    border-left-color: #a5d817;
    box-shadow: 0 10px 26px rgba(165, 216, 23, 0.18);
  }
  .type-top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 8px;
  }
  .type-name {
    font-weight: 600;
    color: #111827;
    font-size: 16px;
    margin: 0;
  }
  .badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 12px;
    background: #f3f4f6;
    color: #111827;
    white-space: nowrap;
  }
  .rows {
    display: grid;
    gap: 6px;
    color: #374151;
    font-weight: 700;
    font-size: 13px;
  }
  .row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
  }
  .row strong { color: #0a1347; }
  .actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
  }
  .btn-primaryish {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 12px;
    border-radius: 12px;
    font-weight: 800;
    text-decoration: none;
    background: #a5d817;
    color: #0a1347;
    border: 1px solid #91bf14;
  }
  .btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 12px;
    border-radius: 12px;
    font-weight: 800;
    text-decoration: none;
    background: #fff;
    color: #0a1347;
    border: 1px solid #d1d5db;
  }
  .history {
    margin-top: 18px;
    border-top: 1px solid #e5e7eb;
    padding-top: 14px;
  }
  .history h2 {
    margin: 0 0 10px 0;
    font-weight: 700;
    color: #2c3e50;
    font-size: 18px;
  }
  .history-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
  }
  .history-table th, .history-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 700;
    font-size: 13px;
    color: #111827;
    vertical-align: top;
  }
  .history-table th {
    background: #f9fafb;
    font-weight: 800;
    color: #2c3e50;
  }
  .status-pill {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 999px;
    font-weight: 800;
    font-size: 12px;
    background: #eef2ff;
    color: #1e3a8a;
  }
  .status-pill.approved { background: #ecfdf5; color: #065f46; }
</style>

<div class="balance-wrap">
  <div class="balance-header">
    <h1>Leave Balance{!! !empty($selectedType) ? ': <span style="color:#6b7280; font-weight:700;">' . e($selectedType) . '</span>' : '' !!}</h1>
    <form class="balance-year" method="GET" action="{{ route('leave.balance') }}">
      <label for="year">Year</label>
      <input id="year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" />
      @if(!empty($selectedType))
        <input type="hidden" name="type" value="{{ $selectedType }}" />
      @endif
      <button type="submit" class="btn-ghost">Go</button>
    </form>
  </div>

  <div class="grid">
    @foreach($leaveBalances as $type => $info)
      @php
        $isActive = !empty($selectedType) && strtolower((string) $selectedType) === strtolower((string) $type);
        $remaining = $info['remaining_days'] ?? 0;
      @endphp
      <div class="type-card {{ $isActive ? 'active' : '' }}" id="type-{{ \Illuminate\Support\Str::slug($type) }}">
        <div class="type-top">
          <p class="type-name">{{ $type }} Leave</p>
          <span class="badge">{{ rtrim(rtrim(number_format((float) $remaining, 1), '0'), '.') }} days left</span>
        </div>
        <div class="rows">
          <div class="row"><span>Allocated</span><strong>{{ rtrim(rtrim(number_format((float) ($info['default_days'] ?? 0), 1), '0'), '.') }}</strong></div>
          <div class="row"><span>Taken</span><strong>{{ rtrim(rtrim(number_format((float) ($info['taken_days'] ?? 0), 1), '0'), '.') }}</strong></div>
          <div class="row"><span>Half days</span><strong>{{ !empty($info['allow_half_days']) ? 'Allowed' : 'Not allowed' }}</strong></div>
          <div class="row"><span>Proof</span><strong>{{ !empty($info['requires_proof']) ? 'Required' : 'Not required' }}</strong></div>
        </div>
        <div class="actions">
          <a class="btn-primaryish" href="{{ route('apply', ['leave_type' => $type]) }}">Apply</a>
          @if(empty($selectedType))
            <a class="btn-ghost" href="{{ route('leave.balance', ['year' => $year, 'type' => $type]) }}">Details</a>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <div class="history">
    <h2>Approved leave taken ({{ $year }}){!! !empty($selectedType) ? ': <span style="color:#6b7280; font-weight:700;">' . e($selectedType) . '</span>' : '' !!}</h2>
    <table class="history-table">
      <thead>
        <tr>
          <th style="width: 160px;">Type</th>
          <th style="width: 140px;">Start</th>
          <th style="width: 140px;">End</th>
          <th style="width: 120px;">Days</th>
          <th style="width: 120px;">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($leavesTakenRaw as $leave)
          @php
            if (!empty($selectedType) && strtolower((string) $selectedType) !== strtolower((string) $leave->leave_type)) {
              continue;
            }
          @endphp
          <tr>
            <td>{{ $leave->leave_type }}</td>
            <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') }}</td>
            <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}</td>
            <td>{{ rtrim(rtrim(number_format((float) ($leave->days ?? 0), 1), '0'), '.') }}</td>
            <td><span class="status-pill approved">Approved</span></td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="text-align:center; color:#6b7280; font-weight: 800;">No approved leave found for this year.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

