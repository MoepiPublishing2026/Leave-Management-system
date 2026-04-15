@extends('layouts.app')

@section('title', 'Leave Application')

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
      }
      form.leave-form {
          gap: 16px 16px;
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
  .file-upload {
      flex: 1 1 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      border: 2px dashed #55771dff;
      border-radius: 8px;
      color: #436e0bff;
      font-size: 14px;
      cursor: pointer;
      position: relative;
      text-align: center;
      user-select: none;
      transition: border-color 0.25s;
  }
  .file-upload.required {
      border-color: #cf2727 !important;
  }
  .file-upload:hover {
      background-color: #f9f9f9;
  }
  .file-upload svg {
      width: 36px;
      height: 36px;
      margin-bottom: 10px;
      fill: #888;
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
  #file-upload-filename {
      margin-top: 10px;
      color: #355900;
      font-weight: 600;
      font-size: 13px;
      overflow-wrap: break-word;
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
  form.leave-form .button-group .submit-btn {
      background-color: #b2cd16;
      color: #1b1b17;
      box-shadow: 0px 10px 50px #b2cd160f;
      transition: background-color 0.25s;
  }
  form.leave-form .button-group .submit-btn:hover {
      background-color: #d5e02d;
  }
  form.leave-form .button-group .cancel-btn {
      background-color: #0a1347;
      color: #eff106;
      box-shadow: 0px 10px 50px #9f9f9f4f;
      transition: background-color 0.25s;
  }
  form.leave-form .button-group .cancel-btn:hover {
      background-color: #7a7a7a;
      color: white;
  }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="main-content">
    <h1>Leave Application</h1>
    <h2>Fill in the form to request a leave</h2>

    <form class="leave-form" method="POST" action="{{ route('leave.submit') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <label class="section-title" style="flex-basis:100%;font-size:16px;margin-bottom:5px;">Leave Info</label>
        <div class="input-group">
            <label for="leave_type">Types of Leaves:</label>
            <select id="leave_type" name="leave_type" required>
                <option value="">Select Leave Type</option>
                @php $selectedLeaveType = old('leave_type', request('leave_type')); @endphp
                @foreach(\App\Models\LeavePolicy::where('leave_type', '!=', 'Urgent')->get() as $policy)
                    <option value="{{ $policy->leave_type }}" {{ $selectedLeaveType == $policy->leave_type ? 'selected' : '' }}>
                    {{ $policy->leave_type }}</option>
                @endforeach
            </select>
        </div>

        <div class="input-group" style="flex: 1 1 45%;">
            <label for="start_date">Start Date:</label>
            <input id="start_date" name="start_date" type="text" value="{{ old('start_date') }}" required />
        </div>

        <div class="input-group" style="flex: 1 1 45%;">
            <label for="end_date">End Date:</label>
            <input id="end_date" name="end_date" type="text" value="{{ old('end_date') }}" />
        </div>

        <div class="input-group" style="flex-basis: 100%; flex-direction: row; align-items: center; gap: 10px;">
            <input type="checkbox" id="half_day" name="half_day" value="1" style="width: auto;" {{ old('half_day') ? 'checked' : '' }}>
            <label for="half_day" style="margin-bottom: 0;">Half-Day Request (End Date will be ignored)</label>
        </div>

        <div class="input-group file-upload" id="file-upload-container">
            <div style="font-weight: 700; margin-bottom: 10px; color: #2d3748;">Attachment</div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#888" aria-hidden="true">
                <path d="M9 16h6v-2H9v2zm-2-6h10v-2H7v2zm1-6h8v2H8V4zm6 16h-4v-2h4v2zm-2-6l-4 4h3v4h2v-4h3l-4-4z"/>
            </svg>
            <input type="file" name="file_uploads[]" aria-label="Drag and drop or select files" multiple />
            <div id="file-upload-filename"></div>
        </div>

        <div class="input-group" style="flex-basis: 100%;">
            <label for="important_comment">Important Comment:</label>
            <textarea id="important_comment" name="important_comment" placeholder="Add any important comments here...">{{ old('important_comment') }}</textarea>
        </div>

        <div class="button-group">
            <button type="button" class="cancel-btn" onclick="window.location.href='{{ url()->previous() }}'">Cancel</button>
            <button type="submit" class="submit-btn" id="submit-btn">Submit Application</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const publicHolidays = @json($publicHolidays);
    const leaveBalancesByYear = @json($leaveBalancesByYear);
    const nowYear = @json($nowYear);
    const allowedWhenAnnualExhausted = ['Sick', 'Bereavement', 'Maternity'];

    const disableDates = [
        function(date) { return (date.getDay() === 0 || date.getDay() === 6); },
        ...publicHolidays
    ];

    flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        disable: disableDates,
        minDate: "today"
    });

    flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        disable: disableDates,
        minDate: "today"
    });

    function calculateWorkingDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        const start = new Date(startDate);
        const end = new Date(endDate);
        let count = 0;
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const day = d.getDay();
            const isoDate = d.toISOString().slice(0,10);
            if (day !== 0 && day !== 6 && !publicHolidays.includes(isoDate)) {
                count++;
            }
        }
        return count;
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

    const form = document.querySelector('form.leave-form');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const leaveType = document.getElementById('leave_type').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const halfDay = document.getElementById('half_day').checked;
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
        if (!startDate || (!endDate && !halfDay)) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select both start and end dates.',
                confirmButtonText: 'OK'
            });
            return;
        }
        if (leaveType === 'Unpaid' && comment.length < 3) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Reason',
                text: 'Please provide a valid reason for unpaid leave.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const startYear = startDate ? new Date(startDate).getFullYear() : null;
        const endYear = endDate ? new Date(endDate).getFullYear() : null;
        if (startYear && endYear && startYear !== endYear) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Range',
                text: 'Start Date and End Date must be within the same year.',
                confirmButtonText: 'OK'
            });
            return false;
        }

        let daysRequested = calculateWorkingDays(startDate, endDate);
        if (halfDay && calculateWorkingDays(startDate, startDate) > 0) {
            daysRequested = 0.5;
        }
        const yearKey = startYear || nowYear;
        const leaveBalances = leaveBalancesByYear[yearKey] || {};
        const annualBalance = leaveBalances['Annual'] ?? leaveBalances['Annual Leave'] ?? 0;
        const balance = leaveBalances[leaveType] ?? 0;

        if (annualBalance <= 0 && leaveType && !allowedWhenAnnualExhausted.includes(leaveType)) {
            Swal.fire({
                icon: 'error',
                title: 'Annual Leave Limit Reached',
                html: `You have exhausted your annual leave limit. You may only request <b>Sick</b> (with medical certificate), <b>Bereavement</b> (with proof), or <b>Maternity</b> (with proof).`,
                confirmButtonText: 'OK'
            });
            return false;
        }

        if (leaveType && !allowedWhenAnnualExhausted.includes(leaveType) && daysRequested > annualBalance) {
            Swal.fire({
                icon: 'error',
                title: 'Annual Leave Balance Exceeded',
                html: `Your remaining annual leave balance is <b>${annualBalance}</b> day${annualBalance === 1 ? '' : 's'}. You requested <b>${daysRequested}</b> day${daysRequested === 1 ? '' : 's'} (excluding weekends and public holidays).`,
                confirmButtonText: 'OK'
            });
            return false;
        }

        if (daysRequested > balance) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Leave Balance',
                html: `Your remaining balance for <b>${leaveType || 'this leave type'}</b> is <b>${balance}</b> day${balance === 1 ? '' : 's'}. You requested <b>${daysRequested}</b> day${daysRequested === 1 ? '' : 's'} (excluding weekends and public holidays).`,
                confirmButtonText: 'OK'
            });
            return false;
        }

        e.target.submit();
    });

    const fileUploadContainer = document.getElementById('file-upload-container');
    const fileUploadInput = fileUploadContainer.querySelector('input[type="file"]');
    const fileUploadFilename = document.getElementById('file-upload-filename');
    const leaveTypeSelect = document.getElementById('leave_type');
    const startDateInput = document.getElementById('start_date');

    fileUploadInput.addEventListener('change', function() {
        if (fileUploadInput.files.length > 0) {
            const fileNames = Array.from(fileUploadInput.files).map(f => f.name).join(', ');
            fileUploadFilename.textContent = fileNames;
        } else {
            fileUploadFilename.textContent = '';
        }
    });

    function updateFileUploadRequirement() {
        const requiresProof = @json(\App\Models\LeavePolicy::all()->keyBy('leave_type')->map->requires_proof);
        if (requiresProof[leaveTypeSelect.value]) {
            fileUploadInput.required = true;
            fileUploadContainer.classList.add('required');
        } else {
            fileUploadInput.required = false;
            fileUploadContainer.classList.remove('required');
        }
    }
    leaveTypeSelect.addEventListener('change', updateFileUploadRequirement);
    updateFileUploadRequirement();

    function updateHalfDayEligibility() {
        const allowHalfDays = @json(\App\Models\LeavePolicy::all()->keyBy('leave_type')->map->allow_half_days);
        const isAllowed = !!allowHalfDays[leaveTypeSelect.value];
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

    function updateAnnualEligibility() {
        const startDate = startDateInput.value;
        const yearKey = startDate ? new Date(startDate).getFullYear() : nowYear;
        const leaveBalances = leaveBalancesByYear[yearKey] || {};
        const annualBalance = leaveBalances['Annual'] ?? leaveBalances['Annual Leave'] ?? 0;

        const options = Array.from(leaveTypeSelect.options);
        options.forEach((opt) => {
            if (!opt.value) return;
            if (annualBalance <= 0 && !allowedWhenAnnualExhausted.includes(opt.value)) {
                opt.disabled = true;
            } else {
                opt.disabled = false;
            }
        });

        if (leaveTypeSelect.value && leaveTypeSelect.options[leaveTypeSelect.selectedIndex]?.disabled) {
            leaveTypeSelect.value = '';
        }
    }

    startDateInput.addEventListener('change', updateAnnualEligibility);
    leaveTypeSelect.addEventListener('change', updateAnnualEligibility);
    updateAnnualEligibility();
});
</script>

@endsection
