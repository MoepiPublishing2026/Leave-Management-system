<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\LeavePolicy;
use Carbon\Carbon;
use App\Mail\LeaveRequestSubmittedMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewLeaveRequestNotification;
 use Yasumi\Yasumi;
use Illuminate\Support\Facades\Storage;
use App\Support\PercentageDistribution;


class LeaveController extends Controller
{

private function yearWindow(int $year): array
{
    $start = Carbon::create($year, 1, 1)->startOfDay();
    $end = Carbon::create($year, 12, 31)->endOfDay();
    return [$start, $end];
}

private function buildPublicHolidaysByYear(int $startYear, int $endYear): array
{
    $holidaysByYear = [];
    for ($year = $startYear; $year <= $endYear; $year++) {
        $holidaysByYear[$year] = [];
        $yearHolidays = Yasumi::create('SouthAfrica', $year);
        foreach ($yearHolidays as $holiday) {
            $holidaysByYear[$year][] = $holiday->format('Y-m-d');
        }
    }
    return $holidaysByYear;
}

private function countWorkingDaysInYear(Carbon $start, Carbon $end, array $publicHolidaysForYear, int $year): int
{
    $current = $start->copy();
    $daysCount = 0;
    while ($current->lte($end)) {
        if (
            $current->year === $year &&
            !$current->isWeekend() &&
            !in_array($current->toDateString(), $publicHolidaysForYear)
        ) {
            $daysCount++;
        }
        $current->addDay();
    }
    return $daysCount;
}

   
public function dashboard()
    {
        $userId = auth()->id();

        // Fetch counts by status (case-insensitive)
        $rawStatusCounts = LeaveRequest::where('user_id', $userId)
            ->selectRaw('LOWER(status) as status, COUNT(*) as total')
            ->groupBy(\DB::raw('LOWER(status)'))
            ->pluck('total', 'status')
            ->toArray();

        // Normalize keys to lowercase and fill missing with 0
        $statusCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'withdrawn' => 0];
        foreach ($rawStatusCounts as $status => $count) {
            $statusCounts[strtolower(trim((string) $status))] = $count;
        }

        $pendingTotalCount = $statusCounts['pending'];
        $approvedCount = $statusCounts['approved'];
        $rejectedCount = $statusCounts['rejected'];
        $withdrawnCount = $statusCounts['withdrawn'];

        $overduePendingCount = LeaveRequest::where('user_id', $userId)
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->count();
        $pendingCount = max(0, $pendingTotalCount - $overduePendingCount);

        // Leave type counts
        $leaveTypeCounts = LeaveRequest::where('user_id', $userId)
            ->select('leave_type', \DB::raw('count(*) as total'))
            ->groupBy('leave_type')
            ->pluck('total', 'leave_type')
            ->toArray();
        arsort($leaveTypeCounts, SORT_NUMERIC);
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts);

        // Monthly chart: submissions in the current calendar year only (month-only filter would stack every October, etc.)
        $dashboardYear = (int) Carbon::now()->year;
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $barData = [];
        foreach ($barLabels as $month) {
            $barData[] = LeaveRequest::where('user_id', $userId)
                ->whereYear('created_at', $dashboardYear)
                ->whereMonth('created_at', date('m', strtotime($month . ' 1')))
                ->count();
        }

        // Policies keyed by leave_type
        $policies = LeavePolicy::all()->keyBy('leave_type');

        $year = $dashboardYear;
        [$startOfYear, $endOfYear] = $this->yearWindow($year);

        // Fetch approved leaves intersecting the selected year
        $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereDate('end_date', '>=', $startOfYear->toDateString())
            ->whereDate('start_date', '<=', $endOfYear->toDateString())
            ->get();

        $publicHolidays = $this->generateHolidaysRange($year, $year);

        // Count working leave days excluding weekends and holidays by leave type
        $leaveDaysCounted = [];
        foreach ($leavesTakenRaw as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $daysCount = $this->countWorkingDaysInYear($start, $end, $publicHolidays, $year);
            if (!empty($leave->is_half_day) && $daysCount > 0) {
                $daysCount = 0.5;
            }
            $leaveDaysCounted[$leave->leave_type] = ($leaveDaysCounted[$leave->leave_type] ?? 0) + $daysCount;
        }

        // Calculate leave balances by subtracting working leave days taken
        $leaveBalances = [];
        foreach ($policies as $type => $policy) {
            $taken = $leaveDaysCounted[$type] ?? 0;
            $leaveBalances[$type] = max(0, $policy->default_days - $taken);
        }
        $annualKey = $policies->has('Annual') ? 'Annual' : ($policies->has('Annual Leave') ? 'Annual Leave' : 'Annual');
        if ($policies->has($annualKey)) {
            $annualDefault = $policies[$annualKey]->default_days ?? 30;
            $nonMaternityTaken = 0.0;
            foreach ($leaveDaysCounted as $type => $takenDays) {
                if (strtolower((string) $type) === 'maternity') {
                    continue;
                }
                $nonMaternityTaken += (float) $takenDays;
            }
            $leaveBalances[$annualKey] = max(0, $annualDefault - $nonMaternityTaken);
        }

        // Build utilization dataset (no hardcoded totals)
        $leaveUtilization = [];
        foreach ($policies as $type => $policy) {
            $defaultDays = (float) ($policy->default_days ?? 0);
            $takenDays = (float) ($leaveDaysCounted[$type] ?? 0);
            $remainingDays = (float) max(0, $defaultDays - $takenDays);

            $leaveUtilization[] = [
                'type' => $type,
                'total' => $defaultDays,
                'used' => $takenDays,
                'remaining' => $remainingDays,
            ];
        }
        // Mirror Annual override in utilization too (annual = annualDefault - nonMaternityTaken)
        if ($policies->has($annualKey ?? 'Annual')) {
            $annualKeyResolved = $annualKey ?? 'Annual';
            $annualDefault = (float) ($policies[$annualKeyResolved]->default_days ?? 30);
            $nonMaternityTaken = 0.0;
            foreach ($leaveDaysCounted as $type => $takenDays) {
                if (strtolower((string) $type) === 'maternity') {
                    continue;
                }
                $nonMaternityTaken += (float) $takenDays;
            }
            // replace/insert annual record
            $leaveUtilization = array_values(array_filter($leaveUtilization, fn ($it) => $it['type'] !== $annualKeyResolved));
            $leaveUtilization[] = [
                'type' => $annualKeyResolved,
                'total' => $annualDefault,
                'used' => $nonMaternityTaken,
                'remaining' => (float) max(0, $annualDefault - $nonMaternityTaken),
            ];
        }

        return view('dashboard', compact(
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'withdrawnCount',
            'overduePendingCount',
            'leaveTypeCounts',
            'leaveTypePercentages',
            'year',
            'barLabels',
            'barData',
            'leaveBalances',
            'leaveUtilization'
        ));
    }

public function leaveBalance(Request $request)
{
    $userId = auth()->id();

    $year = (int) $request->query('year', Carbon::now()->year);
    [$startOfYear, $endOfYear] = $this->yearWindow($year);

    $policies = LeavePolicy::all()->keyBy('leave_type');

    $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->orderByDesc('start_date')
        ->get();

    $publicHolidays = $this->generateHolidaysRange($year, $year);

    $leaveDaysCounted = [];
    foreach ($leavesTakenRaw as $leave) {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $daysCount = $this->countWorkingDaysInYear($start, $end, $publicHolidays, $year);
        if (!empty($leave->is_half_day) && $daysCount > 0) {
            $daysCount = 0.5;
        }
        $leaveDaysCounted[$leave->leave_type] = ($leaveDaysCounted[$leave->leave_type] ?? 0) + $daysCount;
        $leave->days = $daysCount;
    }

    $leaveBalances = [];
    foreach ($policies as $type => $policy) {
        $taken = $leaveDaysCounted[$type] ?? 0;
        $leaveBalances[$type] = [
            'default_days' => (float) ($policy->default_days ?? 0),
            'taken_days' => (float) $taken,
            'remaining_days' => (float) max(0, ($policy->default_days ?? 0) - $taken),
            'requires_proof' => (bool) ($policy->requires_proof ?? false),
            'allow_half_days' => (bool) ($policy->allow_half_days ?? false),
        ];
    }

    $annualKey = $policies->has('Annual') ? 'Annual' : ($policies->has('Annual Leave') ? 'Annual Leave' : 'Annual');
    if ($policies->has($annualKey)) {
        $annualDefault = (float) ($policies[$annualKey]->default_days ?? 30);
        $nonMaternityTaken = 0.0;
        foreach ($leaveDaysCounted as $type => $takenDays) {
            if (strtolower((string) $type) === 'maternity') {
                continue;
            }
            $nonMaternityTaken += (float) $takenDays;
        }
        $leaveBalances[$annualKey]['default_days'] = $annualDefault;
        $leaveBalances[$annualKey]['taken_days'] = $nonMaternityTaken;
        $leaveBalances[$annualKey]['remaining_days'] = (float) max(0, $annualDefault - $nonMaternityTaken);
    }

    $selectedType = $request->query('type');
    if (is_string($selectedType)) {
        $selectedType = trim($selectedType);
        if ($selectedType === '') {
            $selectedType = null;
        } else {
            $matchedKey = null;
            foreach (array_keys($leaveBalances) as $key) {
                if (strtolower((string) $key) === strtolower((string) $selectedType)) {
                    $matchedKey = $key;
                    break;
                }
            }
            $selectedType = $matchedKey;
        }
    }

    $approvedLeaves = $leavesTakenRaw;
    if (!empty($selectedType)) {
        $approvedLeaves = $approvedLeaves
            ->filter(fn ($leave) => strtolower((string) $leave->leave_type) === strtolower((string) $selectedType))
            ->values();
    }

    $pendingLeaves = LeaveRequest::where('user_id', $userId)
        ->whereRaw('LOWER(status) = ?', ['pending'])
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->orderBy('start_date')
        ->get();

    foreach ($pendingLeaves as $leave) {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $daysCount = $this->countWorkingDaysInYear($start, $end, $publicHolidays, $year);
        if (!empty($leave->is_half_day) && $daysCount > 0) {
            $daysCount = 0.5;
        }
        $leave->days = $daysCount;
    }

    $requestCounts = [
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'withdrawn' => 0,
        'overdue' => 0,
    ];

    $yearRequests = LeaveRequest::where('user_id', $userId)
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->get();

    foreach ($yearRequests as $req) {
        if (!empty($selectedType) && strtolower((string) $req->leave_type) !== strtolower((string) $selectedType)) {
            continue;
        }

        $statusKey = strtolower(trim((string) $req->status));
        if ($statusKey === 'pending' && Carbon::parse($req->start_date)->lt(Carbon::today())) {
            $requestCounts['overdue']++;
        } elseif (array_key_exists($statusKey, $requestCounts)) {
            $requestCounts[$statusKey]++;
        }
    }

    $annualInfo = $leaveBalances[$annualKey] ?? ['default_days' => 0, 'taken_days' => 0, 'remaining_days' => 0];
    $annualAllocated = (float) ($annualInfo['default_days'] ?? 0);
    $annualTaken = (float) ($annualInfo['taken_days'] ?? 0);
    $annualRemaining = (float) ($annualInfo['remaining_days'] ?? 0);
    $annualUsagePercent = $annualAllocated > 0
        ? min(100, round(($annualTaken / $annualAllocated) * 100))
        : 0;

    $notDisabledWhenAnnualExhausted = ['Annual', 'Annual Leave', 'Sick', 'Bereavement', 'Maternity'];

    $pendingLeavesDisplay = $pendingLeaves;
    if (!empty($selectedType)) {
        $pendingLeavesDisplay = $pendingLeaves
            ->filter(fn ($leave) => strtolower((string) $leave->leave_type) === strtolower((string) $selectedType))
            ->values();
    }

    $pendingDaysTotal = (float) $pendingLeavesDisplay->sum('days');

    $upcomingLeaves = $leavesTakenRaw
        ->filter(fn ($leave) => Carbon::parse($leave->start_date)->gte(Carbon::today()));

    if (!empty($selectedType)) {
        $upcomingLeaves = $upcomingLeaves
            ->filter(fn ($leave) => strtolower((string) $leave->leave_type) === strtolower((string) $selectedType));
    }

    $upcomingLeaves = $upcomingLeaves->sortBy('start_date')->take(5)->values();

    $focusedBalance = null;
    $isTypeDisabled = false;
    $typeUsagePercent = 0;
    $countsAgainstAnnual = false;

    if (!empty($selectedType) && isset($leaveBalances[$selectedType])) {
        $info = $leaveBalances[$selectedType];
        $allocated = (float) ($info['default_days'] ?? 0);
        $taken = (float) ($info['taken_days'] ?? 0);
        $typeUsagePercent = $allocated > 0 ? min(100, (int) round(($taken / $allocated) * 100)) : 0;
        $isTypeDisabled = $annualRemaining <= 0 && !in_array($selectedType, $notDisabledWhenAnnualExhausted, true);
        $countsAgainstAnnual = strtolower((string) $selectedType) !== strtolower((string) $annualKey)
            && strtolower((string) $selectedType) !== 'maternity';

        $focusedBalance = array_merge($info, [
            'type' => $selectedType,
            'usage_percent' => $typeUsagePercent,
        ]);
    }

    $leaveTypeOptions = [];
    foreach ($leaveBalances as $type => $info) {
        $leaveTypeOptions[] = [
            'type' => $type,
            'remaining' => (float) ($info['remaining_days'] ?? 0),
            'disabled' => $annualRemaining <= 0 && !in_array($type, $notDisabledWhenAnnualExhausted, true),
        ];
    }

    return view('leave.balance', compact(
        'year',
        'leaveBalances',
        'leaveTypeOptions',
        'approvedLeaves',
        'selectedType',
        'pendingLeavesDisplay',
        'pendingDaysTotal',
        'requestCounts',
        'annualKey',
        'annualRemaining',
        'upcomingLeaves',
        'focusedBalance',
        'isTypeDisabled',
        'typeUsagePercent',
        'countsAgainstAnnual',
        'notDisabledWhenAnnualExhausted'
    ));
}


   

public function generateHolidaysRange($startYear, $endYear)
{
    $holidays = [];
    for ($year = $startYear; $year <= $endYear; $year++) {
        $yearHolidays = Yasumi::create('SouthAfrica', $year);
        foreach ($yearHolidays as $holiday) {
            $holidays[] = $holiday->format('Y-m-d');
        }
    }
    return $holidays;
}

public function applyForm()
{
    $nowYear = date('Y');
    $futureYearLimit = $nowYear + 10;  // Change limit as needed
    $publicHolidays = $this->generateHolidaysRange($nowYear, $futureYearLimit);

    // Calculate leave balances for the current user, excluding weekends and public holidays
    $userId = auth()->id();
    $policies = LeavePolicy::all()->keyBy('leave_type');

    $holidaysByYear = $this->buildPublicHolidaysByYear((int) $nowYear, (int) $futureYearLimit);

    $rangeStart = Carbon::create((int) $nowYear, 1, 1)->startOfDay();
    $rangeEnd = Carbon::create((int) $futureYearLimit, 12, 31)->endOfDay();

    $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->whereDate('end_date', '>=', $rangeStart->toDateString())
        ->whereDate('start_date', '<=', $rangeEnd->toDateString())
        ->get();

    $leaveDaysCountedByYear = [];
    foreach ($leavesTakenRaw as $leave) {
        if (!empty($leave->is_half_day)) {
            $d = Carbon::parse($leave->start_date);
            $y = $d->year;
            if ($y >= (int) $nowYear && $y <= (int) $futureYearLimit) {
                $holidaysForYear = $holidaysByYear[$y] ?? [];
                if (!$d->isWeekend() && !in_array($d->toDateString(), $holidaysForYear)) {
                    $leaveDaysCountedByYear[$y][$leave->leave_type] = ($leaveDaysCountedByYear[$y][$leave->leave_type] ?? 0) + 0.5;
                }
            }
            continue;
        }

        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);

        $current = $start->copy();
        while ($current->lte($end)) {
            $y = $current->year;
            if ($y < (int) $nowYear || $y > (int) $futureYearLimit) {
                $current->addDay();
                continue;
            }

            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $holidaysForYear = $holidaysByYear[$y] ?? [];
            if (in_array($current->toDateString(), $holidaysForYear)) {
                $current->addDay();
                continue;
            }

            $leaveDaysCountedByYear[$y][$leave->leave_type] = ($leaveDaysCountedByYear[$y][$leave->leave_type] ?? 0) + 1;
            $current->addDay();
        }
    }

    $annualKey = $policies->has('Annual') ? 'Annual' : ($policies->has('Annual Leave') ? 'Annual Leave' : 'Annual');
    $annualDefault = $policies[$annualKey]->default_days ?? 30;

    $leaveBalancesByYear = [];
    for ($year = (int) $nowYear; $year <= (int) $futureYearLimit; $year++) {
        $leaveBalancesByYear[$year] = [];
        foreach ($policies as $type => $policy) {
            $taken = $leaveDaysCountedByYear[$year][$type] ?? 0;
            $leaveBalancesByYear[$year][$type] = max(0, $policy->default_days - $taken);
        }

        $nonMaternityTaken = 0.0;
        foreach (($leaveDaysCountedByYear[$year] ?? []) as $type => $takenDays) {
            if (strtolower((string) $type) === 'maternity') {
                continue;
            }
            $nonMaternityTaken += (float) $takenDays;
        }
        $leaveBalancesByYear[$year][$annualKey] = max(0, $annualDefault - $nonMaternityTaken);
    }

    return view('apply', compact('publicHolidays', 'leaveBalancesByYear', 'nowYear'));
}


 public function submit(Request $request)
{
    $userId = Auth::id();

    $leaveType = $request->input('leave_type');
    $policy = LeavePolicy::where('leave_type', $leaveType)->first();
    $halfDay = $request->boolean('half_day');

    $rules = [
        'leave_type' => 'required|string|max:120',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => $halfDay ? 'nullable|date|after_or_equal:start_date' : 'required|date|after_or_equal:start_date',
        'important_comment' => 'nullable|string|max:512',
        'half_day' => 'nullable|boolean',
    ];

    if ($leaveType === 'Unpaid') {
        $rules['important_comment'] = 'required|string|max:512';
    }

    if ($policy && $policy->requires_proof) {
        $rules['file_uploads'] = 'required|array';
        $rules['file_uploads.*'] = 'file|max:2048';
    } else {
        $rules['file_uploads'] = 'nullable|array';
        $rules['file_uploads.*'] = 'file|max:2048';
    }

    $messages = [
        'leave_type.required' => 'Please select a type of leave.',
        'important_comment.required' => 'A valid reason (comment) is required for unpaid leave.',
        'file_uploads.required' => 'Proof (attachment) is required for this type of leave.',
    ];

    $validated = $request->validate($rules, $messages);

    if ($halfDay && (!$policy || empty($policy->allow_half_days))) {
        return redirect()->back()
            ->withErrors(['half_day' => 'Half-day requests are not allowed for this leave type.'])
            ->withInput();
    }

    if ($halfDay) {
        $validated['end_date'] = $validated['start_date'];
    }

    $startDate = Carbon::parse($validated['start_date']);
    $endDate = Carbon::parse($validated['end_date']);

    $year = $startDate->year;
    if ($endDate->year !== $year) {
        return redirect()->back()
            ->withErrors(['date' => 'Start Date and End Date must be within the same year.'])
            ->withInput();
    }

    $publicHolidays = $this->generateHolidaysRange($year, $year);

    // Calculate requested leave days excluding weekends and public holidays
    $requestedDays = 0;
    $currentDate = $startDate->copy();
    while ($currentDate->lte($endDate)) {
        if (!$currentDate->isWeekend() && !in_array($currentDate->toDateString(), $publicHolidays)) {
            $requestedDays++;
        }
        $currentDate->addDay();
    }

    if ($halfDay && $requestedDays > 0) {
        $requestedDays = 0.5;
    }

    // Calculate leave balances for user excluding weekends and public holidays
    $policies = LeavePolicy::all()->keyBy('leave_type');
    [$startOfYear, $endOfYear] = $this->yearWindow($year);

    $annualKey = $policies->has('Annual') ? 'Annual' : ($policies->has('Annual Leave') ? 'Annual Leave' : 'Annual');
    $annualDefault = $policies[$annualKey]->default_days ?? 30;
    $annualTakenLeaves = LeaveRequest::where('user_id', $userId)
        ->where('status', 'approved')
        ->whereRaw('LOWER(leave_type) != ?', ['maternity'])
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->get();

    $annualTakenDays = 0.0;
    foreach ($annualTakenLeaves as $leave) {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $days = $this->countWorkingDaysInYear($start, $end, $publicHolidays, $year);
        if (!empty($leave->is_half_day) && $days > 0) {
            $days = 0.5;
        }
        $annualTakenDays += $days;
    }

    $annualBalance = max(0, $annualDefault - $annualTakenDays);
    $leaveTypeLower = strtolower(trim((string) $leaveType));
    $allowedWhenAnnualExhausted = ['sick', 'bereavement', 'maternity'];
    if ($annualBalance <= 0 && !in_array($leaveTypeLower, $allowedWhenAnnualExhausted, true)) {
        return redirect()->back()
            ->withErrors([
                'balance' => 'You have exhausted your annual leave limit. You may only request Sick (with medical certificate), Bereavement (with proof), or Maternity (with proof).',
            ])
            ->withInput();
    }
    if (!in_array($leaveTypeLower, $allowedWhenAnnualExhausted, true) && $requestedDays > $annualBalance) {
        return redirect()->back()
            ->withErrors([
                'balance' => "Your remaining annual leave balance is $annualBalance days. You requested $requestedDays days (excluding weekends and public holidays).",
            ])
            ->withInput();
    }

    $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
        ->where('status', 'approved')
        ->where('leave_type', $leaveType)
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->get();

    $takenDays = 0.0;
    foreach ($leavesTakenRaw as $leave) {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $days = $this->countWorkingDaysInYear($start, $end, $publicHolidays, $year);
        if (!empty($leave->is_half_day) && $days > 0) {
            $days = 0.5;
        }
        $takenDays += $days;
    }

    $currentBalance = max(0, ($policies[$leaveType]->default_days ?? 0) - $takenDays);

    if ($requestedDays > $currentBalance) {
        return redirect()->back()
            ->withErrors(['balance' => "Your available leave balance for $leaveType is $currentBalance days. You requested $requestedDays days (excluding weekends and public holidays)."])
            ->withInput();
    }

    if (!$policy || $requestedDays > $policy->default_days) {
        return redirect()->back()
            ->withErrors(['date' => 'You cannot apply for more than ' . ($policy ? $policy->default_days : 0) . ' days for this leave type.'])
            ->withInput();
    }


    // Check for overlapping leave requests
    $overlapExists = LeaveRequest::where('user_id', $userId)
        ->whereIn('status', ['Pending', 'Approved'])
        ->where(function ($query) use ($validated) {
            $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                ->orWhere(function ($subQuery) use ($validated) {
                    $subQuery->where('start_date', '<=', $validated['start_date'])
                        ->where('end_date', '>=', $validated['end_date']);
                });
        })->exists();

    if ($overlapExists) {
        return redirect()->back()
            ->withErrors(['overlap' => 'You have existing leave requests overlapping these dates.'])
            ->withInput();
    }

    // Handle uploaded file
    if ($request->hasFile('file_uploads')) {
        $paths = [];
        foreach ($request->file('file_uploads') as $file) {
            $paths[] = $file->store('leave_files', 'public');
        }
        $validated['file_uploads'] = json_encode($paths);
    } else {
        $validated['file_uploads'] = null;
    }

    $validated['user_id'] = $userId;
    $validated['status'] = 'pending';
    unset($validated['half_day']);
    $validated['is_half_day'] = $halfDay;

    $leaveRequest = LeaveRequest::create($validated);

    // Notify managers by email and Laravel notifications
    $managers = User::where('role', 'manager')->get()->unique('email')->values();

    foreach ($managers as $manager) {
        Mail::to($manager->email)->send(new LeaveRequestSubmittedMail($leaveRequest));
    }
    Notification::send($managers, new NewLeaveRequestNotification($leaveRequest));

    return redirect()->route('apply')->with('success', 'Leave application submitted successfully!');
}


private function calculateWorkingDaysForLeave(LeaveRequest $leave): float
{
    $startYear = (int) Carbon::parse($leave->start_date)->year;
    $endYear = (int) Carbon::parse($leave->end_date)->year;
    $publicHolidays = [];

    for ($year = $startYear; $year <= $endYear; $year++) {
        $holidays = Yasumi::create('SouthAfrica', $year);
        foreach ($holidays as $holiday) {
            $publicHolidays[] = $holiday->format('Y-m-d');
        }
    }

    $start = Carbon::parse($leave->start_date);
    $end = Carbon::parse($leave->end_date);
    $current = $start->copy();
    $daysCount = 0;

    while ($current->lte($end)) {
        if (!$current->isWeekend() && !in_array($current->toDateString(), $publicHolidays, true)) {
            $daysCount++;
        }
        $current->addDay();
    }

    if (!empty($leave->is_half_day) && $daysCount > 0) {
        return 0.5;
    }

    return (float) $daysCount;
}

public function myRequests(Request $request)
{
    $userId = Auth::id();
    $status = strtolower((string) $request->query('status', 'all'));
    $search = trim((string) $request->query('search', ''));
    $searchKey = strtolower($search);

    $query = LeaveRequest::query()
        ->with(['overdueEscalatedBy', 'handledBy'])
        ->where('user_id', $userId)
        ->orderByDesc('created_at');

    if ($status === 'overdue') {
        $query->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today());
    } elseif ($status === 'pending') {
        $query->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '>=', Carbon::today());
    } elseif ($status !== 'all') {
        $query->whereRaw('LOWER(status) = ?', [$status]);
    }

    $effectiveSearch = $search;
    if ($status === 'all' && $searchKey === 'overdue') {
        $query->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today());
        $effectiveSearch = '';
    } elseif ($status === 'all' && $searchKey === 'pending') {
        $query->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '>=', Carbon::today());
        $effectiveSearch = '';
    }

    if ($effectiveSearch !== '') {
        $query->where(function ($q) use ($effectiveSearch) {
            $q->where('leave_type', 'like', '%' . $effectiveSearch . '%')
                ->orWhereRaw('LOWER(status) like ?', ['%' . strtolower($effectiveSearch) . '%'])
                ->orWhere('start_date', 'like', '%' . $effectiveSearch . '%')
                ->orWhere('end_date', 'like', '%' . $effectiveSearch . '%')
                ->orWhere('important_comment', 'like', '%' . $effectiveSearch . '%');

            if (ctype_digit($effectiveSearch)) {
                $q->orWhere('id', (int) $effectiveSearch);
            }
        });
    }

    $leaveRequests = $query->paginate(10)->withQueryString();

    $leaveRequests->getCollection()->transform(function (LeaveRequest $leave) {
        $leave->days = $this->calculateWorkingDaysForLeave($leave);

        return $leave;
    });

    return view('leave.my_requests', compact('leaveRequests', 'status', 'search'));
}


public function destroy($id)
{
    $leaveRequest = LeaveRequest::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    $status = strtolower(trim((string) $leaveRequest->status));
    if (!in_array($status, ['pending', 'withdrawn'], true)) {
        return redirect()
            ->route('leave.myRequests')
            ->with('error', 'Only pending or withdrawn requests can be deleted.');
    }

    try {
        app(\App\Services\UserDeletionService::class)->deleteLeaveRequestRecord($leaveRequest);

        return redirect()
            ->route('leave.myRequests')
            ->with('success', 'Leave request deleted permanently.');
    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('leave.myRequests')
            ->with('error', 'Unable to delete this leave request. Please try again.');
    }
}

public function withdraw($id)
{
    $leaveRequest = LeaveRequest::where('id', $id)
        ->where('user_id', Auth::id())
        ->whereRaw('LOWER(status) = ?', ['pending'])
        ->firstOrFail();

    $leaveRequest->status = 'withdrawn';
    $leaveRequest->save();

    // Fetch all managers
    $managers = \App\Models\User::where('role', 'manager')->get();

    // Send withdrawal notification email to managers
    foreach ($managers as $manager) {
        \Illuminate\Support\Facades\Mail::to($manager->email)->send(new \App\Mail\LeaveWithdrawalMail($leaveRequest));
    }

    return redirect()->route('leave.myRequests')->with('success', 'Leave request withdrawn and managers notified.');
}



public function statusLeaves($status)
{
    return redirect()->route('leave.myRequests', ['status' => strtolower(trim((string) $status))]);
}
public function leavesByDate($date)
{
    $leaves = LeaveRequest::whereDate('start_date', '<=', $date)
        ->whereDate('end_date', '>=', $date)
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->with('user')
        ->get();

    return view('admin.leaves.bydate', compact('leaves', 'date'));
}

public function downloadAttachment(LeaveRequest $leaveRequest, int $attachment)
{
    $user = auth()->user();
    $role = strtolower(trim((string) ($user->role ?? '')));

    if (!in_array($role, ['manager', 'admin'], true) && (int) ($user->id ?? 0) !== (int) $leaveRequest->user_id) {
        abort(403);
    }

    $files = [];
    if (!empty($leaveRequest->file_uploads)) {
        $decoded = json_decode($leaveRequest->file_uploads, true);
        if (is_array($decoded)) {
            $files = $decoded;
        } else {
            $files = [$leaveRequest->file_uploads];
        }
    }

    $idx = max(1, (int) $attachment) - 1;
    $path = $files[$idx] ?? null;
    if (!is_string($path) || trim($path) === '') {
        abort(404);
    }

    $disk = Storage::disk('public');
    $path = ltrim($path, '/');
    if (!$disk->exists($path)) {
        abort(404);
    }

    $fullPath = $disk->path($path);
    $downloadName = basename($path) ?: ('attachment_' . ($idx + 1));

    return response()->download($fullPath, $downloadName);
}

}
