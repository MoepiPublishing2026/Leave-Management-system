@extends($layout ?? 'layouts.app')

@section('title', !empty($editingLeave) ? 'Edit leave request' : 'Leave Application')

@section('content')
<style>
  html, body {
      margin: 0; padding: 0; height: 100%; min-height: 100vh; background: white;
  }
  .main-content {
      margin: 40px auto 0;
      padding: 40px 50px;
      min-height: 100vh;
      width: 100%;
      max-width: 1500px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 0 10px #a7bd8dff;
      box-sizing: border-box;
  }
  @media (max-width: 768px) {
      .main-content {
          margin-top: 18px;
          padding: 18px 14px;
          min-height: auto;
          width: 100%;
          max-width: 100%;
          box-sizing: border-box;
          overflow-x: clip;
      }
      form.leave-form {
          gap: 16px;
          width: 100%;
          max-width: 100%;
          min-width: 0;
          box-sizing: border-box;
      }
      form.leave-form .input-group,
      form.leave-form .input-group--half {
          flex: 1 1 100% !important;
          min-width: 0 !important;
          width: 100% !important;
          max-width: 100% !important;
          box-sizing: border-box;
      }
      form.leave-form .input-group select,
      form.leave-form .input-group .filter-select-wrap,
      form.leave-form .input-group .filter-select__trigger,
      form.leave-form .input-group .filter-select__menu,
      form.leave-form .input-group input[type="text"],
      form.leave-form .input-group textarea {
          width: 100% !important;
          max-width: 100% !important;
          box-sizing: border-box;
      }
      form.leave-form .button-group {
          flex-direction: column;
          gap: 10px;
          margin-top: 20px;
      }
      form.leave-form .button-group button {
          width: 100%;
      }
  }
  .main-content h1 {
      font-weight: 700;
      font-size: 28px;
      color: #4b4b4b;
      margin-bottom: 8px;
  }
  .main-content h2 {
      font-weight: normal;
      color: #b2b6be;
      font-size: 14px;
      margin-bottom: 30px;
  }
  .form-alert {
      margin-bottom: 20px;
      padding: 12px 14px;
      border-radius: 6px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
      font-size: 14px;
  }
  .form-alert ul {
      margin: 6px 0 0;
      padding-left: 18px;
  }
  form.leave-form {
      display: flex;
      flex-wrap: wrap;
      gap: 30px 40px;
      font-size: 14px;
  }
  form.leave-form .input-group {
      flex: 1 1 45%;
      display: flex;
      flex-direction: column;
      min-width: 230px;
      max-width: 100%;
      box-sizing: border-box;
  }
  form.leave-form .input-group--full {
      flex: 1 1 100%;
  }
  form.leave-form .input-group--half {
      flex: 1 1 45%;
  }
  form.leave-form .input-group label {
      margin-bottom: 5px;
      font-weight: 600;
      text-transform: none;
  }
  form.leave-form .input-group select,
  form.leave-form .input-group input[type="text"],
  form.leave-form .input-group textarea {
      padding: 10px 12px;
      border: 1px solid #55771eff;
      border-radius: 6px;
      font-size: 14px;
      color: #333;
      font-family: inherit;
      box-sizing: border-box;
  }
  form.leave-form .input-group textarea {
      resize: vertical;
      min-height: 70px;
      max-height: 150px;
  }
  .file-upload-wrap {
      flex: 1 1 100%;
      display: flex;
      flex-direction: column;
      gap: 8px;
  }
  .file-upload-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
  }
  .file-upload-title {
      font-weight: 700;
      color: #2d3748;
      font-size: 14px;
  }
  .attachment-badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
  }
  .attachment-badge--optional {
      background: #f1f5f9;
      color: #64748b;
      border: 1px solid #cbd5e1;
  }
  .attachment-badge--required {
      background: #fef2f2;
      color: #b91c1c;
      border: 1px solid #fecaca;
  }
  .file-upload {
      border: 2px dashed #55771dff;
      border-radius: 8px;
      background: #fafdf8;
      transition: border-color 0.25s, background 0.25s;
  }
  .file-upload.required {
      border-color: #cf2727 !important;
      background: #fffafa;
  }
  .file-upload.is-dragover {
      border-color: #138056;
      background: #f0faf4;
  }
  .file-upload-zone {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 22px 16px;
      text-align: center;
      cursor: pointer;
      position: relative;
      user-select: none;
  }
  .file-upload-zone:hover {
      background-color: #f9f9f9;
  }
  .file-upload-icon {
      width: 40px;
      height: 40px;
      margin-bottom: 10px;
      fill: #55771d;
      opacity: 0.85;
  }
  .file-upload-text {
      margin: 0 0 6px;
      color: #436e0b;
      font-size: 14px;
  }
  .file-upload-hint {
      margin: 0;
      color: #6b7280;
      font-size: 12px;
      line-height: 1.4;
  }
  .file-upload input[type="file"] {
      position: absolute;
      opacity: 0;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      cursor: pointer;
  }
  .file-upload-list {
      list-style: none;
      margin: 0;
      padding: 0 12px 12px;
      display: flex;
      flex-direction: column;
      gap: 8px;
  }
  .file-upload-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 8px 10px;
      border: 1px solid #d1e7c4;
      border-radius: 6px;
      background: #fff;
      font-size: 13px;
      color: #2d3748;
  }
  .file-upload-item-name {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-weight: 600;
  }
  .file-upload-item-meta {
      color: #6b7280;
      font-size: 12px;
      white-space: nowrap;
  }
  .file-upload-remove {
      border: none;
      background: #fee2e2;
      color: #991b1b;
      font-size: 12px;
      font-weight: 700;
      padding: 4px 8px;
      border-radius: 4px;
      cursor: pointer;
      flex-shrink: 0;
  }
  .file-upload-remove:hover {
      background: #fecaca;
  }
  .file-upload-error {
      margin: 0 12px 12px;
      padding: 8px 10px;
      border-radius: 6px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
      font-size: 12px;
  }
  .parental-leave-note {
      margin: 8px 0 0;
      font-size: 13px;
      color: #64748b;
      line-height: 1.45;
  }
  form.leave-form .button-group {
      flex-basis: 100%;
      margin-top: 40px;
      display: flex;
      gap: 20px;
      justify-content: flex-start;
  }
  form.leave-form .button-group button {
      padding: 12px 30px;
      border-radius: 10px;
      border: none;
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
      user-select: none;
  }
  form.leave-form .button-group .submit-btn,
  form.leave-form .button-group .cancel-btn {
      background-color: #b2cd16;
      color: #0a1347;
      border: 1px solid #91bf14;
      box-shadow: 0 4px 14px rgba(178, 205, 22, 0.2);
      transition: background-color 0.25s;
  }
  form.leave-form .button-group .submit-btn:hover,
  form.leave-form .button-group .cancel-btn:hover {
      background-color: #9eb812;
      color: #0a1347;
  }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@include('partials.flatpickr-grid-fix')

<div class="main-content">
    <h1>{{ !empty($editingLeave) ? 'Edit leave request #'.$editingLeave->id : 'Leave Application' }}</h1>
    <h2>{{ !empty($editingLeave) ? 'Correct the details or replace documents while this request is still pending.' : 'Fill in the form to request a leave' }}</h2>

    @if ($errors->any())
        <div class="form-alert" role="alert">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="leave-form" method="POST" action="{{ route($submitRoute ?? 'leave.submit', $submitRouteParams ?? []) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if (($formMethod ?? 'POST') === 'PUT')
            @method('PUT')
        @endif

        <label class="section-title" style="flex-basis:100%;font-size:16px;margin-bottom:5px;">Leave Info</label>
        <div class="input-group input-group--full">
            <label for="leave_type">Types of Leaves:</label>
            <select id="leave_type" name="leave_type" required>
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

        <div class="input-group" style="flex-basis: 100%; flex-direction: row; align-items: center; gap: 10px;">
            <input type="checkbox" id="half_day" name="half_day" value="1" style="width: auto;" {{ old('half_day', isset($editingLeave) && $editingLeave->is_half_day ? '1' : null) ? 'checked' : '' }}>
            <label for="half_day" style="margin-bottom: 0;">Half-Day Request (End Date will be ignored)</label>
        </div>

        <div class="input-group file-upload-wrap">
            <div class="file-upload-header">
                <span class="file-upload-title">Attachment</span>
                <span class="attachment-badge attachment-badge--optional" id="attachment-badge">Optional</span>
            </div>
            <div class="file-upload" id="file-upload-container">
                <div class="file-upload-zone" id="file-upload-zone">
                    <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm4 18H6V4h7v5h5v11zM8 15.01l1.41 1.41L11 14.84V19h2v-4.16l1.59 1.59L16 15.01 12.01 11 8 15.01z"/>
                    </svg>
                    <p class="file-upload-text"><strong>Click to browse</strong> or drag files here</p>
                    <p class="file-upload-hint" id="file-upload-hint">PDF, JPG, PNG, or Word — up to 10 MB per file. You can attach more than one file.</p>
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
            <label for="important_comment">Reason for leave (Important Comment): <span style="color:#b42318;">*</span></label>
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
    const publicHolidays = @json($publicHolidays);
    const leaveBalancesByYear = @json($leaveBalancesByYear);
    const nowYear = @json($nowYear);
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
            const year = d.getFullYear();
            daysByYear[year] = (daysByYear[year] || 0) + 1;
        }
        return daysByYear;
    }

    const halfDayCheckbox = document.getElementById('half_day');

    halfDayCheckbox.addEventListener('change', function() {
        const endDateInput = document.getElementById('end_date');
        if (this.checked) {
            endDateInput.value = '';
            endDateInput.disabled = true;
        } else {
            endDateInput.disabled = false;
        }
    });
    halfDayCheckbox.dispatchEvent(new Event('change'));

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

        if (rejected.length) {
            showFileError('Some files were not added: ' + rejected.join(', '));
        } else {
            showFileError('');
        }

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

    function updateFileUploadRequirement() {
        const leaveType = leaveTypeSelect.value;
        const needsProofAtSubmit = !!requiresProofByType[leaveType] && !isSickLeaveType(leaveType);
        const isDeferredSick = !!requiresProofByType[leaveType] && isSickLeaveType(leaveType);
        const startDate = startDateInput ? startDateInput.value : '';

        fileUploadInput.removeAttribute('required');

        if (needsProofAtSubmit) {
            const keepingExisting = hasExistingAttachments && selectedFiles.length === 0;
            fileUploadContainer.classList.toggle('required', !keepingExisting);
            attachmentBadge.textContent = keepingExisting ? 'On file' : 'Required';
            attachmentBadge.className = keepingExisting
                ? 'attachment-badge attachment-badge--optional'
                : 'attachment-badge attachment-badge--required';
            fileUploadHint.textContent = keepingExisting
                ? 'Current documents will be kept. Upload new files only if you want to replace them.'
                : 'Supporting document required for this leave type. PDF, JPG, PNG, or Word — up to 10 MB per file.';
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
            fileUploadHint.textContent = 'PDF, JPG, PNG, or Word — up to 10 MB per file. You can attach more than one file.';
        }
    }

    window.validateAttachmentsBeforeSubmit = function () {
        const type = leaveTypeSelect.value;
        const needsProofAtSubmit = requiresProofByType[type] === true || requiresProofByType[type] === 1 || requiresProofByType[type] === '1';
        if (!needsProofAtSubmit || isSickLeaveType(type)) {
            syncFileInput();
            return '';
        }
        if (hasExistingAttachments && selectedFiles.length === 0) {
            return '';
        }
        if (selectedFiles.length === 0) {
            return 'Please attach supporting proof for this leave type.';
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
    flatpickr('#start_date', {
        ...fpOptions,
        onChange: function () {
            updateFileUploadRequirement();
        },
    });
    flatpickr('#end_date', {
        ...fpOptions,
        onChange: function () {
            updateFileUploadRequirement();
        },
    });
    updateFileUploadRequirement();

    function updateHalfDayEligibility() {
        const isAllowed = !!allowHalfDaysByType[leaveTypeSelect.value];
        if (!isAllowed) {
            halfDayCheckbox.checked = false;
            halfDayCheckbox.disabled = true;
            const endDateInput = document.getElementById('end_date');
            endDateInput.disabled = false;
        } else {
            halfDayCheckbox.disabled = false;
        }
    }
    leaveTypeSelect.addEventListener('change', updateHalfDayEligibility);
    updateHalfDayEligibility();

    function updateLeaveTypeEligibility() {
        const startDate = startDateInput.value;
        const yearKey = startDate ? (parseLocalDate(startDate)?.getFullYear() || nowYear) : nowYear;
        const leaveBalances = leaveBalancesByYear[yearKey] || {};
        const annualBalance = leaveBalances['Annual'] ?? leaveBalances['Annual Leave'] ?? 0;

        const options = Array.from(leaveTypeSelect.options);
        options.forEach((opt) => {
            if (!opt.value) return;
            const typeBalance = Number(leaveBalances[opt.value] ?? 0);
            let disabled = typeBalance <= 0;
            let unavailableLabel = disabled ? ' (No balance remaining)' : '';
            if (annualBalance <= 0 && countsAgainstAnnual(opt.value)) {
                disabled = true;
                unavailableLabel = ' (Annual pool exhausted)';
            }
            if (blockedParentalLeaveType && opt.value && opt.value.toLowerCase() === blockedParentalLeaveType.toLowerCase()) {
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
    }

    leaveForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const leaveType = leaveTypeSelect.value;
        const startDate = startDateInput.value;
        const endDate = document.getElementById('end_date').value;
        const halfDay = halfDayCheckbox.checked;
        const comment = (document.getElementById('important_comment')?.value || '').trim();

        if (!leaveType) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Info',
                text: 'Please select a type of leave.',
                confirmButtonText: 'OK'
            });
            return;
        }
        if (blockedParentalLeaveType && leaveType.toLowerCase() === blockedParentalLeaveType.toLowerCase()) {
            Swal.fire({
                icon: 'error',
                title: 'Leave Type Not Available',
                text: `${blockedParentalLeaveType} leave is not available because you already have the other parental leave type on record.`,
                confirmButtonText: 'OK'
            });
            return;
        }
        if (!startDate || (!endDate && !halfDay)) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select both start and end dates.',
                confirmButtonText: 'OK'
            });
            return;
        }
        if (comment.length < 3) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Reason',
                text: 'Please provide a reason for this leave request.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const startYear = startDate ? parseLocalDate(startDate)?.getFullYear() : null;

        let daysRequested = calculateWorkingDays(startDate, endDate);
        let daysByYear = workingDaysByYear(startDate, endDate);
        if (halfDay && calculateWorkingDays(startDate, startDate) > 0) {
            daysRequested = 0.5;
            daysByYear = { [startYear || nowYear]: 0.5 };
        }

        for (const [yearKey, daysInYear] of Object.entries(daysByYear)) {
            if (leaveType === 'Unpaid') {
                continue;
            }
            const leaveBalances = leaveBalancesByYear[yearKey] || {};
            const annualBalance = Number(leaveBalances['Annual'] ?? leaveBalances['Annual Leave'] ?? 0);
            const balance = Number(leaveBalances[leaveType] ?? 0);

            if (countsAgainstAnnual(leaveType)) {
                if (annualBalance <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Annual Leave Limit Reached',
                        html: `You have exhausted your annual leave for <b>${yearKey}</b>. Other leave types use their own balances and can still be requested.`,
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (daysInYear > annualBalance) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Annual Leave Balance Exceeded',
                        html: `Your remaining annual leave balance for <b>${yearKey}</b> is <b>${annualBalance}</b> day${annualBalance === 1 ? '' : 's'}. This request uses <b>${daysInYear}</b> working day${daysInYear === 1 ? '' : 's'} in ${yearKey} (excluding weekends and public holidays).`,
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            }

            if (daysInYear > balance) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Leave Balance',
                    html: `Your remaining <b>${leaveType || 'leave'}</b> balance for <b>${yearKey}</b> is <b>${balance}</b> day${balance === 1 ? '' : 's'}. This request uses <b>${daysInYear}</b> working day${daysInYear === 1 ? '' : 's'} in ${yearKey} (excluding weekends and public holidays).`,
                    confirmButtonText: 'OK'
                });
                return;
            }
        }

        const attachmentError = validateAttachmentsBeforeSubmit();
        if (attachmentError) {
            Swal.fire({
                icon: 'warning',
                title: 'Attachment Required',
                text: attachmentError,
                confirmButtonText: 'OK'
            });
            return;
        }

        const normalizedLeaveType = leaveType.trim().toLowerCase();
        if (normalizedLeaveType === 'maternity' || normalizedLeaveType === 'paternity') {
            const otherParentalLeave = normalizedLeaveType === 'maternity' ? 'Paternity' : 'Maternity';

            Swal.fire({
                icon: 'warning',
                title: `Confirm ${leaveType} leave`,
                html: `You selected <b>${leaveType}</b> leave.<br><br>After submitting, <b>${otherParentalLeave}</b> leave will be unavailable unless you withdraw this request. Is your selection correct?`,
                showCancelButton: true,
                confirmButtonText: `Yes, apply for ${leaveType}`,
                cancelButtonText: 'No, change selection',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('submit-btn').disabled = true;
                    leaveForm.submit();
                }
            });
            return;
        }

        leaveForm.submit();
    });
});
</script>

@endsection
