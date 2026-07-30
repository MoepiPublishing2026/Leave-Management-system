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
use App\Notifications\LeaveWithdrawnNotification;
 use Yasumi\Yasumi;
use Illuminate\Support\Facades\Storage;
use App\Support\AnnualLeaveRules;
use App\Support\LeaveProofRules;
use App\Support\LeaveRequestSearch;
use App\Support\LeavePayFilter;
use App\Support\MonthlyLeaveStatusBreakdown;
use App\Support\LeaveApplicant;
use App\Support\LeaveReminderService;
use App\Support\ParentalLeaveRules;
use App\Support\PersonalLeaveDashboardData;


class LeaveController extends Controller
{
private function myRequestsRoute(): string
{
    return LeaveApplicant::myRequestsRoute(auth()->user());
}

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

private function isLeaveTypeUnavailable(
    string $leaveType,
    float $remaining,
    float $annualRemaining,
    int $userId
): bool {
    $typeExhausted = $remaining <= 0;
    $annualPoolExhausted = AnnualLeaveRules::countsAgainstAnnual($leaveType) && $annualRemaining <= 0;

    return $typeExhausted
        || $annualPoolExhausted
        || ParentalLeaveRules::isBlockedForUser($userId, $leaveType);
}

   
public function dashboard()
    {
        $user = auth()->user();
        if (LeaveApplicant::isManager($user)) {
            return redirect()->route('manager.dashboard');
        }

        LeaveReminderService::syncForUser($user);

        return view('dashboard', array_merge(
            PersonalLeaveDashboardData::build((int) $user->id),
            [
                'layout' => 'layouts.app',
                'pageTitle' => 'Employee Dashboard',
            ]
        ));
    }

public function leaveBalance(Request $request)
{
    $userId = auth()->id();

    $currentYear = (int) Carbon::now()->year;
    $year = (int) $request->query('year', $currentYear);
    $year = max(2000, min(2100, $year));
    $yearOptions = range($currentYear - 4, $currentYear + 1);
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
        $annualPoolUsed = AnnualLeaveRules::sumDaysAgainstAnnual($leaveDaysCounted);
        $leaveBalances[$annualKey]['default_days'] = $annualDefault;
        $leaveBalances[$annualKey]['taken_days'] = $annualPoolUsed;
        $leaveBalances[$annualKey]['remaining_days'] = (float) max(0, $annualDefault - $annualPoolUsed);
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

    if (! empty($selectedType) && ParentalLeaveRules::isBlockedForUser((int) $userId, (string) $selectedType)) {
        return redirect()
            ->route(LeaveApplicant::balanceRoute(), ['year' => $year])
            ->with('error', ParentalLeaveRules::blockedMessage((int) $userId, $selectedType));
    }

    $approvedLeaves = $leavesTakenRaw;
    if (!empty($selectedType)) {
        $approvedLeaves = $approvedLeaves
            ->filter(fn ($leave) => strtolower((string) $leave->leave_type) === strtolower((string) $selectedType))
            ->values();
    }

    $pendingLeaves = LeaveRequest::where('user_id', $userId)
        ->where(function ($q) {
            $q->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->orWhereRaw('LOWER(TRIM(status)) = ?', ['pending_senior']);
        })
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
        } elseif ($statusKey === 'pending_senior') {
            $requestCounts['pending']++;
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
    $typeExhausted = false;
    $typeUsagePercent = 0;
    $countsAgainstAnnual = false;

    if (!empty($selectedType) && isset($leaveBalances[$selectedType])) {
        $info = $leaveBalances[$selectedType];
        $allocated = (float) ($info['default_days'] ?? 0);
        $taken = (float) ($info['taken_days'] ?? 0);
        $remaining = (float) ($info['remaining_days'] ?? 0);
        $typeUsagePercent = $allocated > 0 ? min(100, (int) round(($taken / $allocated) * 100)) : 0;
        $typeExhausted = $remaining <= 0;
        $isTypeDisabled = $this->isLeaveTypeUnavailable(
            (string) $selectedType,
            $remaining,
            $annualRemaining,
            (int) $userId
        );
        $countsAgainstAnnual = AnnualLeaveRules::countsAgainstAnnual((string) $selectedType);

        $focusedBalance = array_merge($info, [
            'type' => $selectedType,
            'usage_percent' => $typeUsagePercent,
        ]);
    }

    $leaveTypeOptions = [];
    foreach ($leaveBalances as $type => $info) {
        $remaining = (float) ($info['remaining_days'] ?? 0);
        $parentalBlocked = ParentalLeaveRules::isBlockedForUser((int) $userId, (string) $type);
        $typeIsExhausted = $remaining <= 0;
        $annualPoolExhausted = AnnualLeaveRules::countsAgainstAnnual((string) $type) && $annualRemaining <= 0;

        $leaveTypeOptions[] = [
            'type' => $type,
            'remaining' => $remaining,
            'disabled' => $this->isLeaveTypeUnavailable(
                (string) $type,
                $remaining,
                $annualRemaining,
                (int) $userId
            ),
            'disabled_reason' => $parentalBlocked
                ? 'This parental leave option is unavailable'
                : ($typeIsExhausted
                    ? 'No balance remaining'
                    : ($annualPoolExhausted ? 'Annual leave pool exhausted' : null)),
        ];
    }

    $user = auth()->user();

    return view('leave.balance', array_merge(compact(
        'year',
        'currentYear',
        'yearOptions',
        'leaveBalances',
        'leaveTypeOptions',
        'approvedLeaves',
        'selectedType',
        'pendingLeavesDisplay',
        'pendingDaysTotal',
        'requestCounts',
        'annualKey',
        'annualRemaining',
        'annualUsagePercent',
        'upcomingLeaves',
        'focusedBalance',
        'isTypeDisabled',
        'typeExhausted',
        'typeUsagePercent',
        'countsAgainstAnnual'
    ), [
        'layout' => LeaveApplicant::layout($user),
        'dashboardRoute' => LeaveApplicant::personalDashboardRoute($user),
        'myRequestsRoute' => LeaveApplicant::myRequestsRoute($user),
        'applyRoute' => LeaveApplicant::applyRoute($user),
        'balanceRoute' => LeaveApplicant::balanceRoute(),
    ]));
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
    $user = auth()->user();
    if (! LeaveApplicant::canApplyLeave($user)) {
        abort(403);
    }
    if (LeaveApplicant::isManager($user) && ! request()->routeIs('manager.*')) {
        return redirect()->route('manager.apply');
    }

    $blockedParentalLeaveType = ParentalLeaveRules::blockedLeaveTypeLabel((int) $user->id);
    $requestedLeaveType = (string) request()->query('leave_type', '');
    if ($blockedParentalLeaveType !== null && $requestedLeaveType !== ''
        && strcasecmp($requestedLeaveType, $blockedParentalLeaveType) === 0) {
        return redirect()->route(LeaveApplicant::applyRoute($user));
    }

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

        $annualPoolUsed = AnnualLeaveRules::sumDaysAgainstAnnual($leaveDaysCountedByYear[$year] ?? []);
        $leaveBalancesByYear[$year][$annualKey] = max(0, $annualDefault - $annualPoolUsed);
    }

    return view('apply', [
        'publicHolidays' => $publicHolidays,
        'leaveBalancesByYear' => $leaveBalancesByYear,
        'nowYear' => $nowYear,
        'layout' => LeaveApplicant::layout($user),
        'submitRoute' => LeaveApplicant::submitRoute($user),
        'approverLabel' => LeaveApplicant::approverLabel($user),
        'isManagerApplicant' => LeaveApplicant::isManager($user),
        'usedParentalLeaveType' => ParentalLeaveRules::usedParentalLeaveType((int) $user->id),
        'blockedParentalLeaveType' => ParentalLeaveRules::blockedLeaveTypeLabel((int) $user->id),
    ]);
}


 public function submit(Request $request)
{
    $user = Auth::user();
    if (! LeaveApplicant::canApplyLeave($user)) {
        abort(403);
    }

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

    if ($policy && $policy->requires_proof && ! LeaveProofRules::allowsDeferredProof($leaveType)) {
        $rules['file_uploads'] = 'required|array|min:1';
        $rules['file_uploads.*'] = 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx';
    } else {
        $rules['file_uploads'] = 'nullable|array';
        $rules['file_uploads.*'] = 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx';
    }

    $messages = [
        'leave_type.required' => 'Please select a type of leave.',
        'important_comment.required' => 'A valid reason (comment) is required for unpaid leave.',
        'file_uploads.required' => 'Proof (attachment) is required for this type of leave.',
        'file_uploads.min' => 'Please attach at least one supporting document.',
        'file_uploads.*.max' => 'Each attachment must be 10 MB or smaller.',
        'file_uploads.*.file' => 'Each attachment must be a valid file.',
        'file_uploads.*.mimes' => 'Attachments must be PDF, JPG, PNG, or Word documents.',
    ];

    $validated = $request->validate($rules, $messages);

    if (! ParentalLeaveRules::canApply((int) $userId, (string) $leaveType)) {
        return redirect()->back()
            ->withErrors(['leave_type' => ParentalLeaveRules::blockedMessage((int) $userId, (string) $leaveType)])
            ->withInput();
    }

    if ($policy && $policy->requires_proof && ! LeaveProofRules::allowsDeferredProof($leaveType) && ! $request->hasFile('file_uploads')) {
        return redirect()->back()
            ->withErrors(['file_uploads' => 'Proof (attachment) is required for this type of leave.'])
            ->withInput();
    }

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
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->get()
        ->filter(fn ($leave) => AnnualLeaveRules::countsAgainstAnnual((string) $leave->leave_type));

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
    if (AnnualLeaveRules::countsAgainstAnnual((string) $leaveType)) {
        if ($annualBalance <= 0) {
            return redirect()->back()
                ->withErrors([
                    'balance' => AnnualLeaveRules::exhaustedAnnualMessage(),
                ])
                ->withInput();
        }
        if ($requestedDays > $annualBalance) {
            return redirect()->back()
                ->withErrors([
                    'balance' => "Your remaining annual leave balance is $annualBalance days. You requested $requestedDays days (excluding weekends and public holidays).",
                ])
                ->withInput();
        }
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
    $validated['status'] = LeaveApplicant::isManager($user) ? 'pending_senior' : 'pending';
    unset($validated['half_day']);
    $validated['is_half_day'] = $halfDay;

    $leaveRequest = LeaveRequest::create($validated);

    if (LeaveApplicant::isManager($user)) {
        try {
            \App\Support\LeaveApprovalWorkflow::notifySeniorsForDecision($leaveRequest->load('user'));
        } catch (\Throwable $e) {
            report($e);
        }
    } else {
        $assignedManager = $user->manager_id
            ? User::query()->managers()->whereKey($user->manager_id)->first()
            : null;
        $managers = $assignedManager
            ? collect([$assignedManager])
            : User::where('role', 'manager')->get()->unique('email')->values();

        foreach ($managers as $manager) {
            try {
                Mail::to($manager->email)->send(new LeaveRequestSubmittedMail($leaveRequest));
            } catch (\Throwable $e) {
                report($e);
            }
        }
        if ($managers->isNotEmpty()) {
            Notification::send($managers, new NewLeaveRequestNotification($leaveRequest));
        }
    }

    $successMessage = 'Leave application submitted successfully!';
    if ($policy && $policy->requires_proof && LeaveProofRules::allowsDeferredProof($leaveType) && ! $request->hasFile('file_uploads')) {
        $proofDeadline = Carbon::parse($validated['start_date'])->addDay()->format('d M Y');
        $approverLabel = LeaveApplicant::approverLabel($user);
        $successMessage = 'Sick leave submitted. Once '.$approverLabel.' approves it, upload your doctor\'s letter from My Leave Requests on your leave start date, by '.$proofDeadline.' at the latest.';
    }

    return redirect()->route(LeaveApplicant::applyRoute($user))->with('success', $successMessage);
}

public function uploadProof(Request $request, $id)
{
    $leaveRequest = LeaveRequest::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    if (! LeaveProofRules::employeeCanManageAttachments($leaveRequest)) {
        return redirect()->route($this->myRequestsRoute())->with('error', 'Attachments cannot be changed for this request.');
    }

    if (! LeaveProofRules::employeeCanSubmitAttachmentNow($leaveRequest)) {
        $deadline = LeaveProofRules::proofDeadlineAt($leaveRequest);
        $message = $deadline
            ? (LeaveProofRules::proofUploadWindowSentence($leaveRequest, 'Attachments can only be uploaded or replaced') ?? 'Attachments cannot be uploaded for this request at this time.')
            : 'Attachments cannot be uploaded for this request at this time.';

        return redirect()->route($this->myRequestsRoute())->with('error', $message);
    }

    $validated = $request->validate([
        'file_uploads' => 'required|array|min:1',
        'file_uploads.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
    ], [
        'file_uploads.required' => 'Please attach at least one supporting document.',
        'file_uploads.min' => 'Please attach at least one supporting document.',
        'file_uploads.*.max' => 'Each attachment must be 10 MB or smaller.',
        'file_uploads.*.mimes' => 'Attachments must be PDF, JPG, PNG, or Word documents.',
    ]);

    $replacing = LeaveProofRules::hasProofAttached($leaveRequest);
    if ($replacing) {
        \App\Support\LeaveAttachmentLinks::deleteStoredFiles($leaveRequest);
    }

    $paths = [];
    foreach ($request->file('file_uploads') as $file) {
        $paths[] = $file->store('leave_files', 'public');
    }

    $leaveRequest->file_uploads = json_encode($paths);
    $leaveRequest->save();

    $message = $replacing
        ? 'Attachment replaced successfully.'
        : (LeaveProofRules::allowsDeferredProof($leaveRequest->leave_type)
            ? 'Doctor\'s letter uploaded successfully.'
            : 'Attachment uploaded successfully.');

    return redirect()->route($this->myRequestsRoute())->with('success', $message);
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
    $user = auth()->user();
    if (! LeaveApplicant::canApplyLeave($user)) {
        abort(403);
    }
    LeaveReminderService::syncForUser($user);
    if (LeaveApplicant::isManager($user) && ! request()->routeIs('manager.*')) {
        return redirect()->route('manager.myLeave', $request->query());
    }

    $userId = Auth::id();
    $status = strtolower((string) $request->query('status', 'all'));
    $payFilter = LeavePayFilter::normalize($request->query('pay', 'all'));
    $search = trim((string) $request->query('search', ''));
    $highlightRequestId = (int) $request->query('request_id', 0);
    if ($highlightRequestId > 0 && $search === '') {
        $search = (string) $highlightRequestId;
    }
    $searchKey = strtolower($search);

    $query = LeaveRequest::query()
        ->with(['overdueEscalatedBy', 'handledBy'])
        ->where('user_id', $userId)
        ->orderByDesc('created_at');
    LeavePayFilter::apply($query, $payFilter);

    if ($status === 'overdue') {
        $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today());
    } elseif ($status === 'pending') {
        // Still "pending" from employee view while with manager or senior manager
        $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                    ->whereDate('start_date', '>=', Carbon::today());
            })->orWhereRaw('LOWER(TRIM(status)) = ?', ['pending_senior']);
        });
    } elseif ($status !== 'all') {
        $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
    }

    $effectiveSearch = $search;
    if ($status === 'all' && $searchKey === 'overdue') {
        $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today());
        $effectiveSearch = '';
    } elseif ($status === 'all' && $searchKey === 'pending') {
        $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                    ->whereDate('start_date', '>=', Carbon::today());
            })->orWhereRaw('LOWER(TRIM(status)) = ?', ['pending_senior']);
        });
        $effectiveSearch = '';
    }

    if ($effectiveSearch !== '') {
        LeaveRequestSearch::apply($query, $effectiveSearch, false);
    }

    $leaveRequests = $query->paginate(10)->withQueryString();

    $leaveRequests->getCollection()->transform(function (LeaveRequest $leave) {
        $leave->days = $this->calculateWorkingDaysForLeave($leave);

        return $leave;
    });

    return view('leave.my_requests', [
        'leaveRequests' => $leaveRequests,
        'status' => $status,
        'payFilter' => $payFilter,
        'search' => $search,
        'layout' => LeaveApplicant::layout($user),
        'formRoute' => LeaveApplicant::myRequestsRoute($user),
        'filterRoute' => LeaveApplicant::myRequestsRoute($user),
        'newRequestRoute' => LeaveApplicant::applyRoute($user),
    ]);
}


public function destroy($id)
{
    $leaveRequest = LeaveRequest::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    if (! $leaveRequest->canBeDeletedByEmployee()) {
        return redirect()
            ->route($this->myRequestsRoute())
            ->with('error', 'This request cannot be deleted.');
    }

    try {
        app(\App\Services\UserDeletionService::class)->deleteLeaveRequestRecord($leaveRequest);

        return redirect()
            ->route($this->myRequestsRoute())
            ->with('success', 'Leave request deleted permanently.');
    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route($this->myRequestsRoute())
            ->with('error', 'Unable to delete this leave request. Please try again.');
    }
}

public function withdraw($id)
{
    $leaveRequest = LeaveRequest::where('id', $id)
        ->where('user_id', Auth::id())
        ->with('user')
        ->firstOrFail();

    if (! $leaveRequest->canBeWithdrawnByEmployee()) {
        $leavePeriodPassed = $leaveRequest->end_date
            && Carbon::parse($leaveRequest->end_date)->startOfDay()->lt(Carbon::today());
        $message = $leavePeriodPassed
            ? 'This leave can no longer be withdrawn because all of its leave days have passed.'
            : 'Only pending or approved leave can be withdrawn.';

        return redirect()->route($this->myRequestsRoute())->with('error', $message);
    }

    $previousStatus = strtolower(trim((string) $leaveRequest->status));
    $leaveRequest->status = 'withdrawn';
    $leaveRequest->save();

    $recipients = collect();
    $applicant = $leaveRequest->user;

    if (! $leaveRequest->isManagerApplicant()) {
        $managerId = $leaveRequest->recommended_by ?: ($applicant->manager_id ?? null);
        $assignedManager = $managerId
            ? User::query()->managers()->whereKey($managerId)->where('is_active', true)->first()
            : null;

        if ($assignedManager) {
            $recipients->push($assignedManager);
        } else {
            $recipients = $recipients->merge(
                User::query()->managers()->where('is_active', true)->get()
            );
        }
    }

    if ($previousStatus === 'approved' || $leaveRequest->isManagerApplicant()) {
        $recipients = $recipients->merge(
            User::query()->where('role', 'admin')->where('is_active', true)->get()
        );
    }

    foreach ($recipients->unique('id')->values() as $recipient) {
        try {
            $recipient->notify(new LeaveWithdrawnNotification($leaveRequest, $previousStatus));
        } catch (\Throwable $e) {
            report($e);
        }

        if (! empty($recipient->email)) {
            try {
                Mail::to($recipient->email)->send(
                    new \App\Mail\LeaveWithdrawalMail($leaveRequest, $previousStatus)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    $notice = $previousStatus === 'approved'
        ? 'Approved leave withdrawn. Your manager and senior managers were notified, and your leave balance was restored.'
        : ($leaveRequest->isManagerApplicant()
            ? 'Leave request withdrawn and senior managers notified.'
            : 'Leave request withdrawn and your manager notified.');

    return redirect()->route($this->myRequestsRoute())->with('success', $notice);
}



public function statusLeaves($status)
{
    return redirect()->route(LeaveApplicant::myRequestsRoute(auth()->user()), [
        'status' => strtolower(trim((string) $status)),
    ]);
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

public function showAttachment(\Illuminate\Http\Request $request, LeaveRequest $leaveRequest, int $attachment)
{
    $user = auth()->user();
    $role = strtolower(trim((string) ($user->role ?? '')));

    if (!in_array($role, ['manager', 'admin'], true) && (int) ($user->id ?? 0) !== (int) $leaveRequest->user_id) {
        abort(403);
    }

    $files = \App\Support\LeaveAttachmentLinks::paths($leaveRequest);

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
    $mime = $disk->mimeType($path) ?: 'application/octet-stream';
    $previewable = \App\Support\LeaveAttachmentLinks::isPreviewablePath($path);
    $forceDownload = $request->boolean('download');

    if ($forceDownload || !$previewable) {
        return response()->download($fullPath, $downloadName, ['Content-Type' => $mime]);
    }

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $downloadName) . '"',
    ]);
}

}
