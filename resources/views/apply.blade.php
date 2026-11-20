@extends($layout ?? 'layouts.app')

@section('title', !empty($editingLeave) ? 'Edit leave request' : 'Leave Application')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  html, body {
      margin: 0; padding: 0; height: 100%; min-height: 100vh; background: #fff;
  }

  /* ---------- White card ---------- */
  .main-content {
      font-family: 'Poppins', 'Segoe UI', sans-serif;
      margin: 18px auto 40px;
      padding: 26px 26px 22px;
      width: calc(100% - 32px);
      max-width: 980px;
      background: #fff;
      border: 1px solid #cfcfcf;
      border-radius: 12px;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
      box-sizing: border-box;
  }
  .main-content h1 {
      font-family: 'Century Gothic', 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 38px;
      color: #545454;
      margin: 0 0 8px;
      line-height: 1.1;
  }
  .main-content h2 {
      font-weight: 400;
      color: #8a8a8a;
      font-size: 13px;
      margin: 0 0 22px;
      padding-left: 4px;
  }

  /* ---------- Form layout ---------- */
  form.leave-form {
      display: flex;
      flex-wrap: wrap;
      gap: 22px 26px;
      font-size: 13px;
      color: #111;
  }
  form.leave-form .section-title {
      flex-basis: 100%;
      font-size: 14px;
      font-weight: 400;
      color: #111;
      margin: 0 0 4px 4px;
  }
  form.leave-form .input-group {
      flex: 1 1 45%;
      display: flex;
      flex-direction: column;
      min-width: 230px;
      max-width: 100%;
      box-sizing: border-box;
  }
  form.leave-form .input-group--full { flex: 1 1 100%; }
  form.leave-form .input-group--half {
      flex: 1 1 calc(50% - 13px) !important;
      width: calc(50% - 13px) !important;
      max-width: calc(50% - 13px) !important;
      min-width: 0 !important;
  }
  form.leave-form .input-group--half input,
  form.leave-form .input-group--half .flatpickr-wrapper {
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
  }
  form.leave-form .input-group label,
  form.leave-form .section-title,
  form.leave-form .file-upload-title,
  form.leave-form .half-day-row label {
      margin-bottom: 8px;
      font-weight: 400;
      font-size: 13px;
      color: #111;
      text-transform: none !important;
      letter-spacing: 0 !important;
  }

  /* ---------- Leave type dropdown (custom, matches the design) ---------- */
  .ltype { position: relative; width: 100%; }
  .ltype-native {
      position: absolute !important;
      width: 1px !important;
      height: 1px !important;
      opacity: 0 !important;
      pointer-events: none !important;
      overflow: hidden;
  }
  /* Hide the app's generic select enhancer if it wraps this select */
  .ltype .filter-select-wrap,
  .ltype .filter-select__trigger,
  .ltype .filter-select__menu { display: none !important; }

  .ltype-trigger {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      height: 42px;
      padding: 0 14px;
      border: 1.5px solid #6b6b6b;
      border-radius: 10px;
      background: #fff;
      font-family: inherit;
      font-size: 12px;
      font-weight: 700;
      color: #111;
      text-align: left;
      cursor: pointer;
      box-sizing: border-box;
  }
  .ltype-trigger:focus-visible { outline: 2px solid #c4d931; outline-offset: 2px; }
  .ltype-chevron { width: 18px; height: 18px; flex-shrink: 0; transition: transform 0.2s ease; }
  .ltype.is-open .ltype-chevron { transform: rotate(180deg); }

  .ltype-menu {
      position: absolute;
      top: calc(100% + 2px);
      left: 0;
      right: 0;
      z-index: 60;
      margin: 0;
      padding: 4px 6px;
      list-style: none;
      background: #ffffff;
      border: 1px solid #e2e2e2;
      border-radius: 0 0 6px 6px;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
      max-height: 230px;
      overflow-y: auto;
  }
  .ltype-menu[hidden] { display: none; }
  .ltype-menu::-webkit-scrollbar { width: 8px; }
  .ltype-menu::-webkit-scrollbar-track { background: #eef2f2; }
  .ltype-menu::-webkit-scrollbar-thumb { background: #a9c3c3; border-radius: 8px; }
  .ltype-option {
      display: block;
      width: 100%;
      margin: 2px 0;
      padding: 7px 12px;
      border: none;
      border-radius: 10px;
      background: transparent;
      font-family: inherit;
      font-size: 15px;
      font-weight: 700;
      color: #2b2b2b;
      text-align: center;
      cursor: pointer;
  }
  .ltype-option:hover,
  .ltype-option:focus-visible { background: #f1f5c4; outline: none; }
  .ltype-option.is-selected { background: #d4e21a; color: #222; }
  .ltype-option:disabled { background: transparent; color: #a3a3a3; cursor: not-allowed; }

  /* Date picker in the same lime theme */
  .flatpickr-day.selected,
  .flatpickr-day.selected:hover { background: #d4e21a; border-color: #d4e21a; color: #111; }
  .flatpickr-day.today { border-color: #a0b026; }

  /* Date inputs + comment: lime border. Left corners stay rounded; the right edge is straight. */
  form.leave-form .input-group input[type="text"],
  form.leave-form .input-group textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1.5px solid #c4d931;
      border-radius: 8px 0 0 8px;
      font-size: 13px;
      color: #333;
      font-family: inherit;
      background: #fff;
      box-sizing: border-box;
      outline: none;
  }
  /* Start Date & End Date border colour */
#start_date,
#end_date,
#important_comment {
    border-color: #c4d931 !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

/* Border colour while the box is clicked/active */
/* #start_date:focus,
#end_date:focus {
    border-color: #a0b026 !important;   /* ← darker shade for focus */
} */
/* Half-day row: box + words on one line */
/* Half-day row: box + words on one line */
.half-day-row {
    flex-basis: 100% !important;
    flex-direction: row !important;
    align-items: center;
    gap: 5px;                       /* words sit close to the box */
}
.half-day-row label {
    margin: 0 !important;
    font-size: 14px;
    line-height: 14px;
    color: #222;
    cursor: pointer;
}

/* The checkbox */
.half-day-row input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    width: 14px !important;
    height: 14px;
    margin: 0;
    flex-shrink: 0;
    border: 1.5px solid #555;       /* thin grey border */
    border-radius: 3px;             /* slightly rounded on all 4 corners */
    background: #fff;
    cursor: pointer;
    position: relative;
}

/* Tick when checked */
.half-day-row input[type="checkbox"]:checked {
    background: #d4e21a;
}
.half-day-row input[type="checkbox"]:checked::after {
    content: '';
    position: absolute;
    left: 3.5px;
    top: 0.5px;
    width: 3px;
    height: 7px;
    border: solid #111;
    border-width: 0 1.5px 1.5px 0;
    transform: rotate(45deg);
}

/* Greyed out when half days aren't allowed */
.half-day-row input[type="checkbox"]:disabled {
    border-color: #b5b5b5;
    background: #f2f2f2;
    /* cursor: not-allowed; */
}
  form.leave-form .input-group input[type="text"] { height: 42px; }
  form.leave-form .input-group input[type="text"]:disabled { background: #f5f5f5; }
  form.leave-form .input-group input[type="text"]:focus,
  form.leave-form .input-group textarea:focus { border-color: #a0b026; }
  form.leave-form .input-group textarea {
      resize: vertical;
      min-height: 56px;
      max-height: 150px;
  }
  form.leave-form .input-group textarea::placeholder { color: #8a8a8a; font-size: 11px; }

  .half-day-row {
      flex-basis: 100% !important;
      flex-direction: row !important;
      align-items: center;
      gap: 8px;
  }
  .half-day-row label { margin-bottom: 0 !important; }
  .half-day-row input[type="checkbox"] { width: 13px; height: 13px; margin: 0; accent-color: #a0b026; }

  .half-day-period-row {
      flex-basis: 100% !important;
      flex-direction: column !important;
      align-items: flex-start !important;
      gap: 8px;
  }
  .half-day-period-label {
      font-size: 13px;
      font-weight: 600;
      color: #333;
  }
  .half-day-period-label .req { color: #c91919; }
  .half-day-period-options {
      display: flex;
      flex-wrap: wrap;
      gap: 10px 16px;
  }
  .half-day-period-option {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin: 0 !important;
      font-size: 13px;
      font-weight: 600;
      color: #222;
      cursor: pointer;
  }
  .half-day-period-option input[type="radio"] {
      width: auto !important;
      margin: 0;
      accent-color: #d4e21a;
  }
  .half-day-period-row .field-error {
      margin: 0;
      color: #c91919;
      font-size: 12px;
  }

  /* ---------- Attachments ---------- */
  .file-upload-wrap {
      flex: 1 1 100% !important;
      display: flex;
      flex-direction: column;
      gap: 10px;
  }
  .file-upload-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
      padding: 0 4px;
  }
  .file-upload-title { font-weight: 700; color: #111; font-size: 13px; }
  .attachment-badge {
      display: inline-block;
      padding: 3px 18px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.03em;
  }
.attachment-badge--optional {
    background: #d5dde0;
    color: #5c656b;
    border: none;
    border-radius: 999px;
    padding: 4px 16px;
}
  .attachment-badge--required { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
  .file-upload {
    border: 2px dashed #c4d931;
    border-radius: 16px;
    background: #ecece3;
    overflow: hidden;
    transition: border-color 0.25s, background 0.25s;
}
  .file-upload.required { border-color: #cf2727 !important; background: #fffafa !important; }
  .file-upload.required .file-upload-icon,
  .file-upload.required .file-upload-text { color: #cf2727 !important; }
  .file-upload.is-dragover { border-color: #a0b026; background: #f4f6e4 !important; }
  .file-upload-zone {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 22px 16px 18px;
      text-align: center;
      cursor: pointer;
      position: relative;
      user-select: none;
  }
  html body main form.leave-form .file-upload {
    background: #ecece3 !important;
    border: 2px dashed #c4d931 !important;
}
html body main form.leave-form .file-upload.required {
    background: #fffafa !important;
    border: 2px dashed #cf2727 !important;
}
html body main form.leave-form .file-upload.required .file-upload-icon,
html body main form.leave-form .file-upload.required .file-upload-text {
    color: #cf2727 !important;
}
html body main form.leave-form .file-upload.is-dragover {
    background: #f4f6e4 !important;
    border-color: #a0b026 !important;
}
  /* Attachments box: force all 4 corners round */
.file-upload,
.file-upload.required,
.file-upload.is-dragover {
    border-radius: 22px !important;
    border-top-left-radius: 22px !important;
    border-top-right-radius: 22px !important;
    border-bottom-left-radius: 22px !important;
    border-bottom-right-radius: 22px !important;
    overflow: hidden;
}

/* Everything inside follows the same curve */
.file-upload-zone {
    border-radius: 20px;
    background: transparent;
}
.file-upload-zone:hover {
    background: transparent;
}
  .file-upload-icon { width: 34px; height: 34px; margin-bottom: 8px; color: #a0b026; }
  .file-upload-text { margin: 0 0 6px; color: #a0b026; font-size: 14px; }
  .file-upload-text strong { font-weight: 700; }
  .file-upload-hint { margin: 0; color: #666; font-size: 11px; line-height: 1.4; }
  .file-upload input[type="file"] {
      position: absolute; opacity: 0; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer;
  }
  .file-upload-list {
      list-style: none; margin: 0; padding: 0 12px 12px;
      display: flex; flex-direction: column; gap: 8px;
  }
  .file-upload-item {
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
      padding: 8px 10px; border: 1px solid #c4d931; border-radius: 6px;
      background: #fff; font-size: 13px; color: #2d3748;
  }
  .file-upload-item-name {
      flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600;
  }
  .file-upload-item-meta { color: #6b7280; font-size: 12px; white-space: nowrap; }
  .file-upload-remove {
      border: none; background: #fee2e2; color: #991b1b; font-size: 12px; font-weight: 700;
      padding: 4px 8px; border-radius: 4px; cursor: pointer; flex-shrink: 0;
  }
  .file-upload-remove:hover { background: #fecaca; }
  .file-upload-error {
      margin: 0 12px 12px; padding: 8px 10px; border-radius: 6px;
      background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 12px;
  }
  .parental-leave-note { margin: 8px 0 0; font-size: 13px; color: #64748b; line-height: 1.45; }

  /* Border color for the important comment textarea */
  #important_comment {
    border-color: #c4d931 !important;   /* border colour */
    min-height: 56px;                   /* height */
    
}
#important_comment:focus {
    border-color: #a0b026 !important;   /* border when clicked */
}
label[for="important_comment"] {
    /* label styles */
}

  /* ---------- Buttons ---------- */
  form.leave-form .button-group {
      flex-basis: 100%;
      margin-top: 18px;
      display: flex;
      gap: 22px;
      justify-content: flex-start;
  }
  html body main form.leave-form .button-group button,
  form.leave-form .button-group button {
      padding: 14px 34px !important;
      min-height: 0 !important;
      height: auto !important;
      width: auto !important;
      border-radius: 12px !important;
      border: none !important;
      background: #d4e21a !important;
      color: #111 !important;
      font-family: inherit;
      font-weight: 700;
      font-size: 13px !important;
      line-height: 1.2 !important;
      box-shadow: none !important;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.2s;
  }
  html body main form.leave-form .button-group .submit-btn,
  form.leave-form .button-group .submit-btn { padding: 14px 48px !important; }
  form.leave-form .button-group button:hover { background: #c3d010 !important; }
  form.leave-form .button-group button:disabled { opacity: 0.6; cursor: not-allowed; }

  /* ---------- Pop-ups (SweetAlert2) ---------- */
  .moepi-swal.swal2-popup {
      width: 690px;
      max-width: calc(100% - 32px);
      border-radius: 12px;
      padding: 24px 30px 34px;
      font-family: 'Poppins', 'Segoe UI', sans-serif;
  }
  .moepi-swal .swal2-icon.moepi-swal-icon {
      border: none !important;
      width: 88px;
      height: 88px;
      margin: 0 auto;
      animation: none !important;
  }
  .moepi-swal .moepi-swal-icon .swal2-icon-content { display: flex; animation: none !important; }
  .moepi-swal .moepi-swal-icon svg { width: 88px; height: 88px; display: block; }
  .moepi-swal .moepi-swal-title {
      font-size: 18px;
      font-weight: 700;
      color: #222;
      text-transform: uppercase;
      padding: 20px 16px 0;
  }
  .moepi-swal .moepi-swal-text {
      font-size: 17px;
      color: #333;
      margin: 12px 40px 0;
      line-height: 1.35;
      white-space: pre-line;
  }
  .moepi-swal .moepi-swal-actions { margin-top: 26px; gap: 12px; }
  .moepi-swal-btn {
      border: none;
      border-radius: 8px;
      padding: 16px 24px;
      min-width: 76px;
      font-family: inherit;
      font-weight: 700;
      font-size: 17px;
      cursor: pointer;
  }
  .moepi-swal-btn:focus { outline: none; box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.08); }
  .moepi-swal-btn--success { background: #d4e21a; color: #222; }
  .moepi-swal-btn--danger  { background: #ff0000; color: #fff; }
  .moepi-swal-btn--ghost   { background: #e9ecef; color: #222; }

  /* ---------- Mobile ---------- */
  @media (max-width: 768px) {
      html body main .main-content,
      html body main.content > .main-content {
          margin: 0 !important;
          padding: 16px 14px 20px !important;
          width: 100% !important;
          max-width: 100% !important;
          overflow-x: clip;
      }
      .main-content h1 { font-size: 28px; }
      form.leave-form { gap: 16px; width: 100%; min-width: 0; }
      form.leave-form .input-group,
      form.leave-form .input-group--half {
          flex: 1 1 100% !important; min-width: 0 !important; width: 100% !important; max-width: 100% !important;
      }
      form.leave-form .button-group { flex-direction: column; gap: 10px; width: 100%; }
      form.leave-form .button-group button { width: 100% !important; }
      .file-upload-hint { overflow-wrap: anywhere; }
  }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@include('partials.flatpickr-grid-fix')

<div class="main-content">
    <h1>{{ !empty($editingLeave) ? 'Edit leave request #'.$editingLeave->id : 'Leave Application' }}</h1>

    <form class="leave-form" method="POST" action="{{ route($submitRoute ?? 'leave.submit', $submitRouteParams ?? []) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if (($formMethod ?? 'POST') === 'PUT')
            @method('PUT')
        @endif

        <label class="section-title">Leave Info</label>

        <div class="input-group input-group--full">
            <label for="leaveTypeTrigger">Types of leaves:</label>
            <div class="ltype" id="leaveTypeDropdown">
            <select id="leave_type" name="leave_type" class="ltype-native" data-filter-select="off" tabindex="-1" aria-hidden="true" required>
                <option value="">Select Leave Type</option>
                @php
                    $selectedLeaveType = old('leave_type', optional($editingLeave ?? null)->leave_type ?? request('leave_type'));
                    if (!empty($blockedParentalLeaveType) && strcasecmp((string) $selectedLeaveType, (string) $blockedParentalLeaveType) === 0) {
                        $selectedLeaveType = '';
                    }
                    $currentYearBalances = $leaveBalancesByYear[$nowYear] ?? [];
                    $currentAnnualBalance = $currentYearBalances['Annual'] ?? $currentYearBalances['Annual Leave'] ?? 0;
                @endphp
                @foreach(\App\Models\LeavePolicy::where('leave_type', '!=', 'Urgent')->get() as $policy)
                    @php
                        $isBlockedParental = !empty($blockedParentalLeaveType)
                            && strcasecmp((string) $policy->leave_type, (string) $blockedParentalLeaveType) === 0;
                        $remainingForType = (float) ($currentYearBalances[$policy->leave_type] ?? 0);
                        $isTypeExhausted = $remainingForType <= 0;
                        $isAnnualExhausted = (float) $currentAnnualBalance <= 0
                            && \App\Support\AnnualLeaveRules::countsAgainstAnnual((string) $policy->leave_type);
                        $isUnavailable = ($isBlockedParental || $isTypeExhausted || $isAnnualExhausted)
                            && strcasecmp((string) $selectedLeaveType, (string) $policy->leave_type) !== 0;
                    @endphp
                    <option value="{{ $policy->leave_type }}" data-label="{{ $policy->leave_type }}"
                        @selected($selectedLeaveType == $policy->leave_type)
                        @disabled($isUnavailable)>
                    {{ $policy->leave_type }}{{ $isTypeExhausted ? ' (No balance remaining)' : ($isAnnualExhausted ? ' (Annual pool exhausted)' : '') }}</option>
                @endforeach
            </select>
                <button type="button" class="ltype-trigger" id="leaveTypeTrigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="leaveTypeMenu">
                    <span id="leaveTypeTriggerText">Select Leave Type</span>
                    <svg class="ltype-chevron" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <ul class="ltype-menu" id="leaveTypeMenu" role="listbox" aria-label="Types of leaves" hidden></ul>
            </div>
            @if (!empty($usedParentalLeaveType) && !empty($blockedParentalLeaveType))
                <p class="parental-leave-note" id="parental-leave-note">
                    You already have a <strong>{{ $usedParentalLeaveType }}</strong> request on record, so <strong>{{ $blockedParentalLeaveType }}</strong> leave is not available.
                </p>
            @endif
        </div>

        <div class="input-group input-group--half">
            <label for="start_date">Start Date:</label>
            <input id="start_date" name="start_date" type="text" value="{{ old('start_date', isset($editingLeave) ? $editingLeave->start_date : '') }}" required />
        </div>

        <div class="input-group input-group--half">
            <label for="end_date">End Date:</label>
            <input id="end_date" name="end_date" type="text" value="{{ old('end_date', isset($editingLeave) && empty($editingLeave->is_half_day) ? $editingLeave->end_date : '') }}" />
        </div>

        <div class="input-group half-day-row">
            <input type="checkbox" id="half_day" name="half_day" value="1" style="width: auto;" {{ old('half_day', isset($editingLeave) && $editingLeave->is_half_day ? '1' : null) ? 'checked' : '' }}>
            <label for="half_day">Half-Day Request (End Date will be ignored)</label>
        </div>

        @php
            $halfDayPeriodOld = old('half_day_period', isset($editingLeave) ? ($editingLeave->half_day_period ?? '') : '');
            $halfDayPeriods = \App\Support\LeaveWorkdayRules::halfDayPeriods();
        @endphp
        <div class="input-group half-day-period-row" id="half_day_period_row" hidden>
            <span class="half-day-period-label">Half-day period <span class="req">*</span></span>
            <div class="half-day-period-options" role="radiogroup" aria-label="Half-day period">
                @foreach ($halfDayPeriods as $periodValue => $periodLabel)
                    <label class="half-day-period-option">
                        <input
                            type="radio"
                            name="half_day_period"
                            value="{{ $periodValue }}"
                            {{ $halfDayPeriodOld === $periodValue ? 'checked' : '' }}
                        >
                        <span>{{ $periodLabel }}</span>
                    </label>
                @endforeach
            </div>
            @error('half_day_period')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="input-group file-upload-wrap">
            <div class="file-upload-header">
                <span class="file-upload-title">Attachments</span>
                <span class="attachment-badge attachment-badge--optional" id="attachment-badge">Optional</span>
            </div>
            <div class="file-upload" id="file-upload-container">
                <div class="file-upload-zone" id="file-upload-zone">
                    <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 3v5h5"/>
                        <path d="M12 16v-5"/>
                        <path d="M9.5 13.5 12 11l2.5 2.5"/>
                        <path d="M9 18.5h6"/>
                    </svg>
                    <p class="file-upload-text"><strong>Click to browse</strong> or drag file here</p>
                    <p class="file-upload-hint" id="file-upload-hint">PDF, JPG, PNG, or Word - up to 10 MB per file. You can attach more than one file</p>
                    <input
                        type="file"
                        id="file_uploads"
                        name="file_uploads[]"
                        aria-label="Upload supporting documents"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,application/pdf,image/jpeg,image/png,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    />
                </div>
                <ul class="file-upload-list" id="file-upload-list" hidden></ul>
                @if (!empty($existingFileLinks))
                    <div class="file-upload-existing" style="padding: 0 16px 16px;">
                        <p class="file-upload-hint" style="margin: 0 0 8px;">Current documents — upload new files only if you want to replace these:</p>
                        <ul class="file-upload-list" style="display:block;">
                            @foreach ($existingFileLinks as $fileLink)
                                <li class="file-upload-item">
                                    <a href="{{ $fileLink['url'] }}" target="_blank" rel="noopener" class="file-upload-item-name">{{ $fileLink['label'] }} — {{ $fileLink['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <p class="file-upload-error" id="file-upload-error" hidden role="alert"></p>
            </div>
        </div>

        <div class="input-group" style="flex-basis: 100%;">
            <label for="important_comment">Reason for leave (Important Comment):</label>
            <textarea id="important_comment" name="important_comment" required minlength="3" maxlength="512" placeholder="Explain why you are taking this leave...">{{ old('important_comment', optional($editingLeave ?? null)->important_comment ?? '') }}</textarea>
        </div>

        <div class="button-group">
            <button type="button" class="cancel-btn" onclick="window.location.href='{{ $cancelUrl ?? url()->previous() }}'">Cancel</button>
            <button type="submit" class="submit-btn" id="submit-btn">{{ !empty($editingLeave) ? 'Save changes' : 'Submit Application' }}</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---------- Pop-ups styled like the design ----------
    const ICON_SUCCESS = '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="32" fill="#00c805"/><path d="M18 33l9 9 19-20" fill="none" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    const ICON_ERROR = '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="32" fill="#f23b3b"/><path d="M21 21l22 22M43 21L21 43" stroke="#fff" stroke-width="8" stroke-linecap="round"/></svg>';

    const swalClasses = (buttonVariant) => ({
        popup: 'moepi-swal',
        icon: 'moepi-swal-icon',
        title: 'moepi-swal-title',
        htmlContainer: 'moepi-swal-text',
        actions: 'moepi-swal-actions',
        confirmButton: `moepi-swal-btn moepi-swal-btn--${buttonVariant}`,
        cancelButton: 'moepi-swal-btn moepi-swal-btn--ghost',
    });

    function showError(message, asHtml = false) {
        return Swal.fire({
            icon: 'error',
            iconHtml: ICON_ERROR,
            title: 'Please fix the following errors:',
            [asHtml ? 'html' : 'text']: message,
            confirmButtonText: 'OK',
            buttonsStyling: false,
            backdrop: 'rgba(30, 30, 30, 0.25)',
            customClass: swalClasses('danger'),
        });
    }

    function showSuccess(message) {
        return Swal.fire({
            icon: 'success',
            iconHtml: ICON_SUCCESS,
            title: 'Success',
            text: message,
            confirmButtonText: 'OK',
            buttonsStyling: false,
            backdrop: 'rgba(30, 30, 30, 0.25)',
            customClass: swalClasses('success'),
        });
    }

    // Server-side messages (e.g. overlapping dates, or a successful submit)
    const serverErrors = @json($errors->all());
    const successMessage = @json(session('success'));
    if (serverErrors.length) {
        showError(serverErrors.join('\n'));
    } else if (successMessage) {
        showSuccess(successMessage);
    }

    const publicHolidays = @json($publicHolidays);
    const leaveBalancesByYear = @json($leaveBalancesByYear);
    const nowYear = @json($nowYear);
    const leaveYearStartMonth = @json($leaveYearStartMonth ?? 5);

    function leaveYearForDate(date) {
        if (!date) return nowYear;
        const d = date instanceof Date ? date : parseLocalDate(date);
        if (!d) return nowYear;
        const month = d.getMonth() + 1;
        return month < leaveYearStartMonth ? d.getFullYear() - 1 : d.getFullYear();
    }

    function leaveYearLabel(year) {
        const y = Number(year);
        if (!Number.isFinite(y)) return String(year);
        return `${y}/${String(y + 1).slice(-2)}`;
    }
    const blockedParentalLeaveType = @json($blockedParentalLeaveType ?? null);
    function countsAgainstAnnual(leaveType) {
        if (!leaveType) return false;
        const annualTypes = @json(\App\Support\AnnualLeaveRules::ANNUAL_TYPES);
        return annualTypes.includes(leaveType.toLowerCase());
    }

    const disableDates = [
        function(date) { return (date.getDay() === 0 || date.getDay() === 6); },
        ...publicHolidays
    ];

    const fpOptions = {
        dateFormat: 'Y-m-d',
        disable: disableDates,
        minDate: @json($datePickerMinDate ?? 'today'),
    };
    const hasExistingAttachments = @json(!empty($hasExistingAttachments));

    function parseLocalDate(isoDate) {
        if (!isoDate) return null;
        const parts = String(isoDate).split('-').map(Number);
        if (parts.length < 3 || parts.some((n) => Number.isNaN(n))) return null;
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatLocalDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function calculateWorkingDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        const start = parseLocalDate(startDate);
        const end = parseLocalDate(endDate);
        if (!start || !end) return 0;
        let count = 0;
        for (let d = new Date(start.getTime()); d <= end; d.setDate(d.getDate() + 1)) {
            const day = d.getDay();
            const isoDate = formatLocalDate(d);
            if (day !== 0 && day !== 6 && !publicHolidays.includes(isoDate)) {
                count++;
            }
        }
        return count;
    }

    function workingDaysByYear(startDate, endDate) {
        const daysByYear = {};
        if (!startDate || !endDate) return daysByYear;
        const start = parseLocalDate(startDate);
        const end = parseLocalDate(endDate);
        if (!start || !end) return daysByYear;
        for (let d = new Date(start.getTime()); d <= end; d.setDate(d.getDate() + 1)) {
            const day = d.getDay();
            const isoDate = formatLocalDate(d);
            if (day === 0 || day === 6 || publicHolidays.includes(isoDate)) {
                continue;
            }
            const year = leaveYearForDate(d);
            daysByYear[year] = (daysByYear[year] || 0) + 1;
        }
        return daysByYear;
    }

    const halfDayCheckbox = document.getElementById('half_day');
    const halfDayPeriodRow = document.getElementById('half_day_period_row');
    const halfDayPeriodInputs = () => Array.from(document.querySelectorAll('input[name="half_day_period"]'));

    function syncHalfDayPeriodUi() {
        const endDateInput = document.getElementById('end_date');
        const checked = !!(halfDayCheckbox && halfDayCheckbox.checked && !halfDayCheckbox.disabled);
        if (endDateInput) {
            if (checked) {
                endDateInput.value = '';
                endDateInput.disabled = true;
            } else {
                endDateInput.disabled = false;
            }
        }
        if (halfDayPeriodRow) {
            halfDayPeriodRow.hidden = !checked;
        }
        halfDayPeriodInputs().forEach((input) => {
            input.disabled = !checked;
            input.required = checked;
            if (!checked) {
                input.checked = false;
            }
        });
    }

    if (halfDayCheckbox) {
        halfDayCheckbox.addEventListener('change', syncHalfDayPeriodUi);
        syncHalfDayPeriodUi();
    }

    const leaveForm = document.querySelector('form.leave-form');
    const fileUploadContainer = document.getElementById('file-upload-container');
    const fileUploadZone = document.getElementById('file-upload-zone');
    const fileUploadInput = document.getElementById('file_uploads');
    const fileUploadList = document.getElementById('file-upload-list');
    const fileUploadError = document.getElementById('file-upload-error');
    const attachmentBadge = document.getElementById('attachment-badge');
    const fileUploadHint = document.getElementById('file-upload-hint');
    const leaveTypeSelect = document.getElementById('leave_type');
    const startDateInput = document.getElementById('start_date');
    const requiresProofByType = @json(\App\Models\LeavePolicy::all()->keyBy('leave_type')->map->requires_proof);
    const allowHalfDaysByType = @json(\App\Models\LeavePolicy::all()->keyBy('leave_type')->map->allow_half_days);
    const approverLabel = @json($approverLabel ?? 'your manager');

    const DEFAULT_HINT = 'PDF, JPG, PNG, or Word - up to 10 MB per file. You can attach more than one file';
    const MAX_FILE_BYTES = 10 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    let selectedFiles = [];

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function isAllowedFile(file) {
        const name = (file.name || '').toLowerCase();
        const ext = name.includes('.') ? name.split('.').pop() : '';
        return ALLOWED_EXTENSIONS.includes(ext);
    }

    function showFileError(message) {
        if (!fileUploadError) return;
        fileUploadError.textContent = message;
        fileUploadError.hidden = !message;
    }

    function syncFileInput() {
        if (typeof DataTransfer === 'undefined') {
            renderFileList();
            return;
        }
        const dt = new DataTransfer();
        selectedFiles.forEach((file) => dt.items.add(file));
        fileUploadInput.files = dt.files;
        renderFileList();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderFileList() {
        if (!fileUploadList) return;

        if (!selectedFiles.length) {
            fileUploadList.innerHTML = '';
            fileUploadList.hidden = true;
            return;
        }

        fileUploadList.hidden = false;
        fileUploadList.innerHTML = selectedFiles.map((file, index) => {
            const safeName = escapeHtml(file.name);
            return `<li class="file-upload-item">
                <span class="file-upload-item-name" title="${safeName}">${safeName}</span>
                <span class="file-upload-item-meta">${formatFileSize(file.size)}</span>
                <button type="button" class="file-upload-remove" data-index="${index}">Remove</button>
            </li>`;
        }).join('');

        fileUploadList.querySelectorAll('.file-upload-remove').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const idx = parseInt(btn.dataset.index, 10);
                selectedFiles.splice(idx, 1);
                showFileError('');
                syncFileInput();
                updateFileUploadRequirement();
            });
        });
    }

    function addFiles(fileList) {
        const rejected = [];

        Array.from(fileList || []).forEach((file) => {
            if (!isAllowedFile(file)) {
                rejected.push(`${file.name} (unsupported type)`);
                return;
            }
            if (file.size > MAX_FILE_BYTES) {
                rejected.push(`${file.name} (over 10 MB)`);
                return;
            }
            const duplicate = selectedFiles.some((f) => f.name === file.name && f.size === file.size);
            if (!duplicate) {
                selectedFiles.push(file);
            }
        });

        showFileError(rejected.length ? 'Some files were not added: ' + rejected.join(', ') : '');
        syncFileInput();
        updateFileUploadRequirement();
    }

    fileUploadInput.addEventListener('change', function () {
        if (fileUploadInput.files && fileUploadInput.files.length) {
            addFiles(fileUploadInput.files);
        }
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        fileUploadZone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            fileUploadContainer.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        fileUploadZone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            fileUploadContainer.classList.remove('is-dragover');
        });
    });

    fileUploadZone.addEventListener('drop', (event) => {
        addFiles(event.dataTransfer.files);
    });

    function isSickLeaveType(type) {
        return String(type || '').toLowerCase() === 'sick';
    }

    function isBackdatedLeaveSelection() {
        const startDate = startDateInput ? startDateInput.value : '';
        if (!startDate) {
            return false;
        }
        const endDateInput = document.getElementById('end_date');
        const endDate = (endDateInput && endDateInput.value) ? endDateInput.value : startDate;
        const endDay = new Date(endDate + 'T00:00:00');
        if (isNaN(endDay.getTime())) {
            return false;
        }
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return endDay < today;
    }

    function leaveTypeNeedsProof(type) {
        return requiresProofByType[type] === true
            || requiresProofByType[type] === 1
            || requiresProofByType[type] === '1';
    }

    function needsProofAtSubmitForSelection() {
        const leaveType = leaveTypeSelect.value;
        if (!leaveTypeNeedsProof(leaveType)) {
            return false;
        }
        if (!isSickLeaveType(leaveType)) {
            return true;
        }
        // Backdated sick leave: letter must be attached now (already saw a doctor).
        return isBackdatedLeaveSelection();
    }

    function updateFileUploadRequirement() {
        const leaveType = leaveTypeSelect.value;
        const needsProofAtSubmit = needsProofAtSubmitForSelection();
        const isDeferredSick = leaveTypeNeedsProof(leaveType)
            && isSickLeaveType(leaveType)
            && !isBackdatedLeaveSelection();
        const startDate = startDateInput ? startDateInput.value : '';

        fileUploadInput.removeAttribute('required');

        if (needsProofAtSubmit) {
            const keepingExisting = hasExistingAttachments && selectedFiles.length === 0;
            const isBackdatedSick = isSickLeaveType(leaveType) && isBackdatedLeaveSelection();
            fileUploadContainer.classList.toggle('required', !keepingExisting);
            attachmentBadge.textContent = keepingExisting ? 'On file' : 'Required';
            attachmentBadge.className = keepingExisting
                ? 'attachment-badge attachment-badge--optional'
                : 'attachment-badge attachment-badge--required';
            if (keepingExisting) {
                fileUploadHint.textContent = 'Current documents will be kept. Upload new files only if you want to replace them.';
            } else if (isBackdatedSick) {
                fileUploadHint.textContent = 'Doctor\'s letter required for backdated sick leave — attach it now. PDF, JPG, PNG, or Word - up to 10 MB per file.';
            } else {
                fileUploadHint.textContent = 'Supporting document required for this leave type. PDF, JPG, PNG, or Word - up to 10 MB per file.';
            }
        } else if (isDeferredSick) {
            fileUploadContainer.classList.remove('required');
            attachmentBadge.textContent = 'Optional now';
            attachmentBadge.className = 'attachment-badge attachment-badge--optional';
            let hint = `Submit without a doctor's letter. Once ${approverLabel} approves, upload it from My Leave Requests by the day after your leave ends.`;
            const endDateInput = document.getElementById('end_date');
            const endDate = endDateInput ? endDateInput.value : '';
            const deadlineSource = endDate || startDate;
            if (deadlineSource) {
                const anchor = new Date(deadlineSource + 'T00:00:00');
                if (!isNaN(anchor.getTime())) {
                    const deadline = new Date(anchor);
                    deadline.setDate(deadline.getDate() + 1);
                    const deadlineLabel = deadline.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                    hint = `Submit without a doctor's letter. After approval, upload it from My Leave Requests by ${deadlineLabel} at the latest (the day after your leave ends). You may attach it now if you already have it.`;
                }
            }
            fileUploadHint.textContent = hint;
        } else {
            fileUploadContainer.classList.remove('required');
            attachmentBadge.textContent = 'Optional';
            attachmentBadge.className = 'attachment-badge attachment-badge--optional';
            fileUploadHint.textContent = DEFAULT_HINT;
        }
    }

    window.validateAttachmentsBeforeSubmit = function () {
        const type = leaveTypeSelect.value;
        const needsProofAtSubmit = needsProofAtSubmitForSelection();
        if (!needsProofAtSubmit) {
            syncFileInput();
            return '';
        }
        if (hasExistingAttachments && selectedFiles.length === 0) {
            return '';
        }
        if (selectedFiles.length === 0) {
            return isSickLeaveType(type) && isBackdatedLeaveSelection()
                ? 'Please attach a doctor\'s letter for backdated sick leave.'
                : 'Please attach supporting proof for this leave type.';
        }
        syncFileInput();
        if (fileUploadInput.files && fileUploadInput.files.length > 0) {
            return '';
        }
        return 'Your attachment could not be read. Please select the file again.';
    };

    leaveTypeSelect.addEventListener('change', updateFileUploadRequirement);
    if (startDateInput) {
        startDateInput.addEventListener('change', updateFileUploadRequirement);
    }
    const endDateInputForProof = document.getElementById('end_date');
    if (endDateInputForProof) {
        endDateInputForProof.addEventListener('change', updateFileUploadRequirement);
    }
    flatpickr('#start_date', { ...fpOptions, onChange: updateFileUploadRequirement });
    flatpickr('#end_date', { ...fpOptions, onChange: updateFileUploadRequirement });
    updateFileUploadRequirement();

    function updateHalfDayEligibility() {
        const isAllowed = !!allowHalfDaysByType[leaveTypeSelect.value];
        if (!isAllowed) {
            halfDayCheckbox.checked = false;
            halfDayCheckbox.disabled = true;
            document.getElementById('end_date').disabled = false;
        } else {
            halfDayCheckbox.disabled = false;
        }
        syncHalfDayPeriodUi();
    }
    leaveTypeSelect.addEventListener('change', updateHalfDayEligibility);
    updateHalfDayEligibility();

    // ---------- Custom leave type dropdown ----------
    const ltypeRoot = document.getElementById('leaveTypeDropdown');
    const ltypeTrigger = document.getElementById('leaveTypeTrigger');
    const ltypeTriggerText = document.getElementById('leaveTypeTriggerText');
    const ltypeMenu = document.getElementById('leaveTypeMenu');

    function renderLeaveTypeDropdown() {
        const selected = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        ltypeTriggerText.textContent = selected ? selected.textContent.trim() : 'Select Leave Type';

        ltypeMenu.innerHTML = '';
        Array.from(leaveTypeSelect.options).forEach((opt) => {
            const isSelected = opt.value === leaveTypeSelect.value;
            const li = document.createElement('li');
            li.setAttribute('role', 'none');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ltype-option' + (isSelected ? ' is-selected' : '');
            btn.textContent = opt.textContent.trim();
            btn.dataset.value = opt.value;
            btn.disabled = opt.disabled;
            btn.setAttribute('role', 'option');
            btn.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            li.appendChild(btn);
            ltypeMenu.appendChild(li);
        });
    }

    function openLeaveTypeMenu() {
        renderLeaveTypeDropdown();
        ltypeMenu.hidden = false;
        ltypeRoot.classList.add('is-open');
        ltypeTrigger.setAttribute('aria-expanded', 'true');
        const current = ltypeMenu.querySelector('.ltype-option.is-selected:not(:disabled)')
            || ltypeMenu.querySelector('.ltype-option:not(:disabled)');
        if (current) {
            current.focus();
            current.scrollIntoView({ block: 'nearest' });
        }
    }

    function closeLeaveTypeMenu(returnFocus) {
        if (ltypeMenu.hidden) return;
        ltypeMenu.hidden = true;
        ltypeRoot.classList.remove('is-open');
        ltypeTrigger.setAttribute('aria-expanded', 'false');
        if (returnFocus) ltypeTrigger.focus();
    }

    ltypeTrigger.addEventListener('click', () => {
        if (ltypeMenu.hidden) openLeaveTypeMenu();
        else closeLeaveTypeMenu(false);
    });
    ltypeTrigger.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            openLeaveTypeMenu();
        }
    });
    ltypeMenu.addEventListener('click', (event) => {
        const btn = event.target.closest('.ltype-option');
        if (!btn || btn.disabled) return;
        leaveTypeSelect.value = btn.dataset.value;
        leaveTypeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        closeLeaveTypeMenu(true);
    });
    ltypeMenu.addEventListener('keydown', (event) => {
        const items = Array.from(ltypeMenu.querySelectorAll('.ltype-option:not(:disabled)'));
        const index = items.indexOf(document.activeElement);
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            items[Math.min(index + 1, items.length - 1)]?.focus();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            items[Math.max(index - 1, 0)]?.focus();
        } else if (event.key === 'Escape') {
            event.preventDefault();
            closeLeaveTypeMenu(true);
        } else if (event.key === 'Tab') {
            closeLeaveTypeMenu(false);
        }
    });
    document.addEventListener('click', (event) => {
        if (!ltypeRoot.contains(event.target)) closeLeaveTypeMenu(false);
    });

    function updateLeaveTypeEligibility() {
        const startDate = startDateInput.value;
        const yearKey = startDate ? (leaveYearForDate(startDate) || nowYear) : nowYear;
        const leaveBalances = leaveBalancesByYear[yearKey] || {};
        const annualBalance = leaveBalances['Annual'] ?? leaveBalances['Annual Leave'] ?? 0;

        Array.from(leaveTypeSelect.options).forEach((opt) => {
            if (!opt.value) return;
            const typeBalance = Number(leaveBalances[opt.value] ?? 0);
            let disabled = typeBalance <= 0;
            let unavailableLabel = disabled ? ' (No balance remaining)' : '';
            if (annualBalance <= 0 && countsAgainstAnnual(opt.value)) {
                disabled = true;
                unavailableLabel = ' (Annual pool exhausted)';
            }
            if (blockedParentalLeaveType && opt.value.toLowerCase() === blockedParentalLeaveType.toLowerCase()) {
                disabled = true;
                unavailableLabel = ' (Unavailable)';
            }
            opt.disabled = disabled;
            opt.textContent = `${opt.dataset.label || opt.value}${unavailableLabel}`;
        });

        if (leaveTypeSelect.value && leaveTypeSelect.options[leaveTypeSelect.selectedIndex]?.disabled) {
            leaveTypeSelect.value = '';
        }

        if (typeof window.syncFilterSelectOptions === 'function') {
            window.syncFilterSelectOptions(leaveTypeSelect);
        }
        renderLeaveTypeDropdown();
    }

    startDateInput.addEventListener('change', updateLeaveTypeEligibility);
    leaveTypeSelect.addEventListener('change', updateLeaveTypeEligibility);
    updateLeaveTypeEligibility();

    if (blockedParentalLeaveType && leaveTypeSelect.value
        && leaveTypeSelect.value.toLowerCase() === blockedParentalLeaveType.toLowerCase()) {
        leaveTypeSelect.value = '';
        if (typeof window.syncFilterSelectOptions === 'function') {
            window.syncFilterSelectOptions(leaveTypeSelect);
        }
        renderLeaveTypeDropdown();
    }

    leaveForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const leaveType = leaveTypeSelect.value;
        const startDate = startDateInput.value;
        const endDate = document.getElementById('end_date').value;
        const halfDay = halfDayCheckbox.checked;
        const comment = (document.getElementById('important_comment')?.value || '').trim();

        if (!leaveType) {
            showError('Please select a type of leave.');
            return;
        }
        if (blockedParentalLeaveType && leaveType.toLowerCase() === blockedParentalLeaveType.toLowerCase()) {
            showError(`${blockedParentalLeaveType} leave is not available because you already have the other parental leave type on record.`);
            return;
        }
        if (!startDate || (!endDate && !halfDay)) {
            showError('Please select both start and end dates.');
            return;
        }
        if (halfDay && !halfDayPeriodInputs().some((input) => input.checked)) {
            showError('Please choose a half-day period: 08:00 – 12:00 or 12:00 – 17:00.');
            return;
        }
        if (comment.length < 3) {
            showError('Please provide a reason for this leave request.');
            return;
        }

        const startYear = startDate ? leaveYearForDate(startDate) : null;

        let daysByYear = workingDaysByYear(startDate, endDate);
        if (halfDay && calculateWorkingDays(startDate, startDate) > 0) {
            daysByYear = { [startYear || nowYear]: 0.5 };
        }

        for (const [yearKey, daysInYear] of Object.entries(daysByYear)) {
            if (leaveType === 'Unpaid') {
                continue;
            }
            const leaveBalances = leaveBalancesByYear[yearKey] || {};
            const annualBalance = Number(leaveBalances['Annual'] ?? leaveBalances['Annual Leave'] ?? 0);
            const balance = Number(leaveBalances[leaveType] ?? 0);
            const safeType = escapeHtml(leaveType || 'leave');

            if (countsAgainstAnnual(leaveType)) {
                if (annualBalance <= 0) {
                    showError(`You have exhausted your annual leave for <b>${leaveYearLabel(yearKey)}</b>. Other leave types use their own balances and can still be requested.`, true);
                    return;
                }
                if (daysInYear > annualBalance) {
                    showError(`Your remaining annual leave balance for <b>${leaveYearLabel(yearKey)}</b> is <b>${annualBalance}</b> day${annualBalance === 1 ? '' : 's'}. This request uses <b>${daysInYear}</b> working day${daysInYear === 1 ? '' : 's'} in ${leaveYearLabel(yearKey)} (excluding weekends and public holidays).`, true);
                    return;
                }
            }

            if (daysInYear > balance) {
                showError(`Your remaining <b>${safeType}</b> balance for <b>${leaveYearLabel(yearKey)}</b> is <b>${balance}</b> day${balance === 1 ? '' : 's'}. This request uses <b>${daysInYear}</b> working day${daysInYear === 1 ? '' : 's'} in ${leaveYearLabel(yearKey)} (excluding weekends and public holidays).`, true);
                return;
            }
        }

        const attachmentError = validateAttachmentsBeforeSubmit();
        if (attachmentError) {
            showError(attachmentError);
            return;
        }

        const normalizedLeaveType = leaveType.trim().toLowerCase();
        if (normalizedLeaveType === 'maternity' || normalizedLeaveType === 'paternity') {
            const otherParentalLeave = normalizedLeaveType === 'maternity' ? 'Paternity' : 'Maternity';
            const safeType = escapeHtml(leaveType);

            Swal.fire({
                icon: 'warning',
                title: `Confirm ${leaveType} leave`,
                html: `You selected <b>${safeType}</b> leave.<br><br>After submitting, <b>${otherParentalLeave}</b> leave will be unavailable unless you withdraw this request. Is your selection correct?`,
                showCancelButton: true,
                confirmButtonText: `Yes, apply for ${leaveType}`,
                cancelButtonText: 'No, change selection',
                reverseButtons: true,
                focusCancel: true,
                buttonsStyling: false,
                backdrop: 'rgba(30, 30, 30, 0.25)',
                customClass: { ...swalClasses('success'), icon: '' },
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('submit-btn').disabled = true;
                    leaveForm.submit();
                }
            });
            return;
        }

        document.getElementById('submit-btn').disabled = true;
        leaveForm.submit();
    });
});
</script>

@endsection