<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\LeavePolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeaveRequestUpdatedNotification;
use App\Notifications\NewLeaveRequestNotification;
use App\Notifications\LeaveWithdrawnNotification;
use App\Notifications\LeaveSubmittedNotification;
use App\Notifications\ProofUploadedNotification;
use Illuminate\Support\Facades\Storage;
use App\Support\AnnualLeaveRules;
use App\Support\LeaveProofRules;
use App\Support\LeaveRequestSearch;
use App\Support\LeavePayFilter;
use App\Support\MonthlyLeaveStatusBreakdown;
use App\Support\LeaveApplicant;
use App\Support\LeavePortalLinks;
use App\Support\LeaveReminderService;
use App\Support\MissingProofUnpaidConverter;
use App\Support\OverdueLeaveNotifier;
use App\Support\ParentalLeaveRules;
use App\Support\LeaveYear;
use App\Support\PersonalLeaveDashboardData;
use App\Services\PublicHolidayService;


class LeaveController extends Controller
{
private function myRequestsRoute(): string
{
    return LeaveApplicant::myRequestsRoute(auth()->user());
}

private function yearWindow(int $year): array
{
    return LeaveYear::window($year);
}

private function buildPublicHolidaysByYear(int $startYear, int $endYear): array
{
    return PublicHolidayService::datesByYear($startYear, $endYear);
}

private function countWorkingDaysInYear(Carbon $start, Carbon $end, array $publicHolidaysForYear, int $year): int
{
    [$yearStart, $yearEnd] = $this->yearWindow($year);
    $windowStart = $yearStart->copy()->startOfDay();
    $windowEnd = $yearEnd->copy()->startOfDay();
    $current = $start->copy()->startOfDay();
    $last = $end->copy()->startOfDay();
    $daysCount = 0;
    while ($current->lte($last)) {
        if (
            $current->betweenIncluded($windowStart, $windowEnd) &&
            !$current->isWeekend() &&
            !in_array($current->toDateString(), $publicHolidaysForYear)
        ) {
            $daysCount++;
        }
        $current->addDay();
    }
    return $daysCount;
}

/**
 * @return array<int, float>
 */
private function workingDaysByYear(Carbon $start, Carbon $end, array $holidays, bool $halfDay = false): array
{
    if ($halfDay) {
        $year = LeaveYear::forDate($start);
        $dayIsWorking = ! $start->isWeekend() && ! in_array($start->toDateString(), $holidays, true);

        return $dayIsWorking ? [$year => 0.5] : [$year => 0.0];
    }

    $daysByYear = [];
    $current = $start->copy()->startOfDay();
    $last = $end->copy()->startOfDay();
    while ($current->lte($last)) {
        if (! $current->isWeekend() && ! in_array($current->toDateString(), $holidays, true)) {
            $year = LeaveYear::forDate($current);
            $daysByYear[$year] = ($daysByYear[$year] ?? 0) + 1;
        }
        $current->addDay();
    }

    return $daysByYear;
}

private function remainingDaysInYear(
    int $userId,
    string $leaveType,
    int $year,
    float $defaultDays,
    array $holidays,
    bool $useAnnualPool = false
): float {
    [$yearStart, $yearEnd] = $this->yearWindow($year);

    $leaves = LeaveRequest::where('user_id', $userId)
        ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
        ->whereDate('end_date', '>=', $yearStart->toDateString())
        ->whereDate('start_date', '<=', $yearEnd->toDateString())
        ->get();

    if ($useAnnualPool) {
        $leaves = $leaves->filter(fn ($leave) => AnnualLeaveRules::countsAgainstAnnual((string) $leave->leave_type));
    } else {
        $leaves = $leaves->where('leave_type', $leaveType);
    }

    $takenDays = 0.0;
    foreach ($leaves as $leave) {
        $days = $this->countWorkingDaysInYear(
            Carbon::parse($leave->start_date),
            Carbon::parse($leave->end_date),
            $holidays,
            $year
        );
        if (! empty($leave->is_half_day) && $days > 0) {
            $days = 0.5;
        }
        $takenDays += $days;
    }

    return max(0, $defaultDays - $takenDays);
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
        try {
            OverdueLeaveNotifier::notifyDue();
        } catch (\Throwable $e) {
            report($e);
        }

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

    $currentYear = LeaveYear::current();
    $year = (int) $request->query('year', $currentYear);
    $year = max(2000, min(2100, $year));
    $yearOptions = LeaveYear::options($currentYear);
    [$startOfYear, $endOfYear] = $this->yearWindow($year);

    $policies = LeavePolicy::all()->keyBy('leave_type');

    $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->whereDate('end_date', '>=', $startOfYear->toDateString())
        ->whereDate('start_date', '<=', $endOfYear->toDateString())
        ->orderByDesc('start_date')
        ->get();

    $publicHolidays = $this->generateHolidaysRange((int) $startOfYear->year, (int) $endOfYear->year);

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
        if ($req->isOverdue()) {
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
        'yearLabel' => LeaveYear::label($year),
        'yearRangeLabel' => LeaveYear::rangeLabel($year),
        'layout' => LeaveApplicant::layout($user),
        'dashboardRoute' => LeaveApplicant::personalDashboardRoute($user),
        'myRequestsRoute' => LeaveApplicant::myRequestsRoute($user),
        'applyRoute' => LeaveApplicant::applyRoute($user),
        'balanceRoute' => LeaveApplicant::balanceRoute(),
    ]));
}


   

public function generateHolidaysRange($startYear, $endYear)
{
    return PublicHolidayService::datesForYearRange((int) $startYear, (int) $endYear);
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

    return view('apply', $this->applyFormData($user));
}

public function edit($id)
{
    $user = auth()->user();
    if (! LeaveApplicant::canApplyLeave($user)) {
        abort(403);
    }
    if (LeaveApplicant::isManager($user) && ! request()->routeIs('manager.*')) {
        return redirect()->route('manager.leave.edit', $id);
    }

    $leaveRequest = LeaveRequest::query()
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    if (! $leaveRequest->canBeEditedByApplicant()) {
        return redirect()->route($this->myRequestsRoute())
            ->with('error', 'This leave request can no longer be edited because it is no longer pending.');
    }

    return view('apply', $this->applyFormData($user, $leaveRequest));
}

private function applyFormData($user, ?LeaveRequest $editingLeave = null): array
{
    $nowYear = LeaveYear::current();
    $futureYearLimit = $nowYear + 10;
    [$rangeStart] = LeaveYear::window($nowYear);
    [, $rangeEnd] = LeaveYear::window($futureYearLimit);
    $publicHolidays = $this->generateHolidaysRange((int) $rangeStart->year, (int) $rangeEnd->year);
    $userId = (int) $user->id;
    $policies = LeavePolicy::all()->keyBy('leave_type');
    $holidaysByYear = $this->buildPublicHolidaysByYear((int) $rangeStart->year, (int) $rangeEnd->year);

    $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->whereDate('end_date', '>=', $rangeStart->toDateString())
        ->whereDate('start_date', '<=', $rangeEnd->toDateString())
        ->get();

    $leaveDaysCountedByYear = [];
    foreach ($leavesTakenRaw as $leave) {
        if (!empty($leave->is_half_day)) {
            $d = Carbon::parse($leave->start_date);
            $y = LeaveYear::forDate($d);
            if ($y >= (int) $nowYear && $y <= (int) $futureYearLimit) {
                $holidaysForYear = $holidaysByYear[(int) $d->year] ?? [];
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
            $y = LeaveYear::forDate($current);
            if ($y < (int) $nowYear || $y > (int) $futureYearLimit) {
                $current->addDay();
                continue;
            }

            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $holidaysForYear = $holidaysByYear[(int) $current->year] ?? [];
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

    // Allow backdated leave within the previous + current leave years.
    [$retroMin] = LeaveYear::window(LeaveYear::current() - 1);
    $datePickerMinDate = $retroMin->toDateString();
    if ($editingLeave?->start_date) {
        $existingStart = Carbon::parse($editingLeave->start_date);
        if ($existingStart->lt($retroMin)) {
            $datePickerMinDate = $existingStart->toDateString();
        }
    }

    return [
        'publicHolidays' => $publicHolidays,
        'leaveBalancesByYear' => $leaveBalancesByYear,
        'nowYear' => $nowYear,
        'leaveYearStartMonth' => LeaveYear::START_MONTH,
        'layout' => LeaveApplicant::layout($user),
        'submitRoute' => $editingLeave
            ? LeaveApplicant::updateRoute($user)
            : LeaveApplicant::submitRoute($user),
        'submitRouteParams' => $editingLeave ? ['id' => $editingLeave->id] : [],
        'formMethod' => $editingLeave ? 'PUT' : 'POST',
        'editingLeave' => $editingLeave,
        'existingFileLinks' => $editingLeave ? \App\Support\LeaveAttachmentLinks::forRequest($editingLeave) : [],
        'hasExistingAttachments' => $editingLeave ? LeaveProofRules::hasProofAttached($editingLeave) : false,
        'datePickerMinDate' => $datePickerMinDate,
        'approverLabel' => LeaveApplicant::approverLabel($user),
        'isManagerApplicant' => LeaveApplicant::isManager($user),
        'usedParentalLeaveType' => ParentalLeaveRules::usedParentalLeaveType((int) $user->id),
        'blockedParentalLeaveType' => ParentalLeaveRules::blockedLeaveTypeLabel((int) $user->id),
        'cancelUrl' => $editingLeave
            ? route(LeaveApplicant::myRequestsRoute($user), ['status' => 'pending', 'request_id' => $editingLeave->id])
            : url()->previous(),
    ];
}


 public function submit(Request $request)
{
    $user = Auth::user();
    if (! LeaveApplicant::canApplyLeave($user)) {
        abort(403);
    }

    $payload = $this->collectLeaveApplication($request);
    if ($payload instanceof RedirectResponse) {
        return $payload;
    }

    $leaveType = $payload['leave_type'];
    $policy = LeavePolicy::where('leave_type', $leaveType)->first();
    $payload['user_id'] = Auth::id();

    $startDate = Carbon::parse($payload['start_date']);
    $endDate = Carbon::parse($payload['end_date']);
    $isRetroactive = \App\Support\LeaveWorkdayRules::isRetroactiveLeave($startDate, $endDate);

    if ($isRetroactive) {
        $payload['status'] = 'approved';
        $payload['approval_comment'] = \App\Support\LeaveWorkdayRules::backdatedApprovalReason();
    } else {
        $payload['status'] = LeaveApplicant::isManager($user) ? 'pending_senior' : 'pending';
    }

    $leaveRequest = LeaveRequest::create($payload);

    if ($isRetroactive) {
        $leaveRequest->handled_at = Carbon::now();
        $leaveRequest->handled_action = 'auto_approved';
        $leaveRequest->save();
    }

    try {
        $user->notify(new LeaveSubmittedNotification($leaveRequest));
    } catch (\Throwable $e) {
        report($e);
    }

    if ($isRetroactive) {
        $this->notifyReviewersOfRetroactiveApproval($leaveRequest->load('user'));
    } elseif (LeaveApplicant::isManager($user)) {
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

        if ($managers->isNotEmpty()) {
            try {
                Notification::send($managers, new NewLeaveRequestNotification($leaveRequest));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        try {
            \App\Support\LeaveApprovalWorkflow::notifyAssignedSeniorAboutTeamLeave($leaveRequest->load('user'));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    $successMessage = 'Leave application submitted successfully!';
    if ($policy && $policy->requires_proof && LeaveProofRules::allowsDeferredProof($leaveType) && ! $request->hasFile('file_uploads')) {
        $proofDeadlineAnchor = $payload['end_date'] ?? $payload['start_date'];
        $proofDeadline = Carbon::parse($proofDeadlineAnchor)->addDay()->format('d M Y');
        if ($isRetroactive) {
            $successMessage = 'Leave application submitted successfully! Please upload your doctor\'s letter from My Leave Requests by '.$proofDeadline.' at the latest.';
        } else {
            $approverLabel = LeaveApplicant::approverLabel($user);
            $successMessage = 'Sick leave submitted. Once '.$approverLabel.' approves it, upload your doctor\'s letter from My Leave Requests by '.$proofDeadline.' at the latest (the day after your leave ends).';
        }
    }

    return redirect()->route(LeaveApplicant::applyRoute($user))->with('success', $successMessage);
}

public function update(Request $request, $id)
{
    $user = Auth::user();
    if (! LeaveApplicant::canApplyLeave($user)) {
        abort(403);
    }

    $leaveRequest = LeaveRequest::query()
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();

    if (! $leaveRequest->canBeEditedByApplicant()) {
        return redirect()->route($this->myRequestsRoute())
            ->with('error', 'This leave request can no longer be edited because it is no longer pending.');
    }

    $payload = $this->collectLeaveApplication($request, $leaveRequest);
    if ($payload instanceof RedirectResponse) {
        return $payload;
    }

    $payload['start_date'] = Carbon::parse($payload['start_date'])->toDateString();
    $payload['end_date'] = Carbon::parse($payload['end_date'])->toDateString();

    $substantiveChange = strcasecmp((string) $leaveRequest->leave_type, (string) $payload['leave_type']) !== 0
        || Carbon::parse($leaveRequest->start_date)->toDateString() !== $payload['start_date']
        || Carbon::parse($leaveRequest->end_date)->toDateString() !== $payload['end_date']
        || (bool) $leaveRequest->is_half_day !== (bool) $payload['is_half_day']
        || (string) ($leaveRequest->half_day_period ?? '') !== (string) ($payload['half_day_period'] ?? '');

    $wasRecommended = strtolower(trim((string) $leaveRequest->status)) === 'pending_senior'
        && ! $leaveRequest->isManagerApplicant()
        && ! empty($leaveRequest->recommended_by);

    $sentBackToManager = $wasRecommended && $substantiveChange;

    $leaveRequest->fill($payload);

    $becameRetroactive = \App\Support\LeaveWorkdayRules::isRetroactiveLeave(
        Carbon::parse($payload['start_date']),
        Carbon::parse($payload['end_date'])
    );

    if ($becameRetroactive) {
        $leaveRequest->status = 'approved';
        $leaveRequest->handled_at = Carbon::now();
        $leaveRequest->handled_action = 'auto_approved';
        $leaveRequest->approval_comment = \App\Support\LeaveWorkdayRules::backdatedApprovalReason();
        $leaveRequest->manager_recommendation = null;
        $leaveRequest->manager_recommendation_comment = null;
        $leaveRequest->recommended_by = null;
        $leaveRequest->recommended_at = null;
        $leaveRequest->pending_review_reminder_stage = 0;
        $leaveRequest->last_pending_review_reminder_at = null;
    } elseif ($sentBackToManager) {
        $leaveRequest->status = 'pending';
        $leaveRequest->manager_recommendation = null;
        $leaveRequest->manager_recommendation_comment = null;
        $leaveRequest->recommended_by = null;
        $leaveRequest->recommended_at = null;
        $leaveRequest->pending_review_reminder_stage = 0;
        $leaveRequest->last_pending_review_reminder_at = null;
    }

    if (! $leaveRequest->isDirty()) {
        return redirect()->route($this->myRequestsRoute(), [
            'status' => 'pending',
            'request_id' => $leaveRequest->id,
        ])->with('success', 'No changes were made to this leave request.');
    }

    $leaveRequest->save();

    if ($becameRetroactive) {
        $this->notifyReviewersOfRetroactiveApproval($leaveRequest->fresh(['user']));
        $message = 'Leave request updated successfully!';
    } else {
        $this->notifyReviewersOfUpdate($leaveRequest->fresh(['user']), $sentBackToManager);
        $message = $sentBackToManager
            ? 'Leave request updated. Because the dates or leave type changed, it was sent back to your manager for a new recommendation.'
            : 'Leave request updated. Reviewers will see the latest details and documents.';
    }

    return redirect()->route($this->myRequestsRoute(), [
        'status' => $becameRetroactive ? 'approved' : 'pending',
        'request_id' => $leaveRequest->id,
    ])->with('success', $message);
}

/**
 * @return array<string, mixed>|RedirectResponse
 */
private function collectLeaveApplication(Request $request, ?LeaveRequest $existing = null): array|RedirectResponse
{
    $userId = Auth::id();
    $leaveType = $request->input('leave_type');
    $policy = LeavePolicy::where('leave_type', $leaveType)->first();
    $halfDay = $request->boolean('half_day');
    $hasExistingFiles = $existing ? LeaveProofRules::hasProofAttached($existing) : false;
    $startForProof = $request->input('start_date');
    $endForProof = $halfDay
        ? ($request->input('end_date') ?: $startForProof)
        : $request->input('end_date');
    $requiresProofAtSubmit = LeaveProofRules::requiresProofAtSubmit($leaveType, $startForProof, $endForProof);
    $isBackdatedSickProof = $requiresProofAtSubmit
        && LeaveProofRules::allowsDeferredProof($leaveType)
        && LeaveProofRules::requiresProof($leaveType);
    $proofRequiredMessage = $isBackdatedSickProof
        ? LeaveProofRules::backdatedSickProofMessage()
        : 'Proof (attachment) is required for this type of leave.';

    // Past dates are allowed for backdated / emergency leave.
    $rules = [
        'leave_type' => 'required|string|max:120',
        'start_date' => 'required|date',
        'end_date' => $halfDay ? 'nullable|date|after_or_equal:start_date' : 'required|date|after_or_equal:start_date',
        'important_comment' => 'required|string|min:3|max:512',
        'half_day' => 'nullable|boolean',
        'half_day_period' => $halfDay ? 'required|in:morning,afternoon' : 'nullable|in:morning,afternoon',
        'file_uploads' => ($requiresProofAtSubmit && ! $hasExistingFiles) ? 'required|array|min:1' : 'nullable|array',
        'file_uploads.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
    ];

    $validated = $request->validate($rules, [
        'leave_type.required' => 'Please select a type of leave.',
        'important_comment.required' => 'Please provide a reason for this leave request.',
        'important_comment.min' => 'Please provide a valid reason (at least 3 characters).',
        'half_day_period.required' => 'Please choose a half-day period: 08:00 – 12:00 or 12:00 – 17:00.',
        'half_day_period.in' => 'Please choose a half-day period: 08:00 – 12:00 or 12:00 – 17:00.',
        'file_uploads.required' => $proofRequiredMessage,
        'file_uploads.min' => $isBackdatedSickProof
            ? LeaveProofRules::backdatedSickProofMessage()
            : 'Please attach at least one supporting document.',
        'file_uploads.*.max' => 'Each attachment must be 10 MB or smaller.',
        'file_uploads.*.file' => 'Each attachment must be a valid file.',
        'file_uploads.*.mimes' => 'Attachments must be PDF, JPG, PNG, or Word documents.',
    ]);

    if (! ParentalLeaveRules::canApply((int) $userId, (string) $leaveType)) {
        return redirect()->back()
            ->withErrors(['leave_type' => ParentalLeaveRules::blockedMessage((int) $userId, (string) $leaveType)])
            ->withInput();
    }

    if ($requiresProofAtSubmit && ! $request->hasFile('file_uploads') && ! $hasExistingFiles) {
        return redirect()->back()
            ->withErrors(['file_uploads' => $proofRequiredMessage])
            ->withInput();
    }

    if ($halfDay && (! $policy || empty($policy->allow_half_days))) {
        return redirect()->back()
            ->withErrors(['half_day' => 'Half-day requests are not allowed for this leave type.'])
            ->withInput();
    }

    if ($halfDay) {
        $validated['end_date'] = $validated['start_date'];
        if (! \App\Support\LeaveWorkdayRules::isValidHalfDayPeriod($validated['half_day_period'] ?? null)) {
            return redirect()->back()
                ->withErrors(['half_day_period' => 'Please choose a half-day period: 08:00 – 12:00 or 12:00 – 17:00.'])
                ->withInput();
        }
    } else {
        $validated['half_day_period'] = null;
    }

    $startDate = Carbon::parse($validated['start_date']);
    $endDate = Carbon::parse($validated['end_date']);

    [$earliestAllowed] = LeaveYear::window(LeaveYear::current() - 1);
    if ($startDate->lt($earliestAllowed) && ! ($existing && $existing->start_date && Carbon::parse($existing->start_date)->lte($startDate))) {
        return redirect()->back()
            ->withErrors(['start_date' => 'Leave cannot start before '.$earliestAllowed->format('d M Y').' (previous leave year).'])
            ->withInput();
    }

    $existingStart = ($existing && $existing->start_date)
        ? Carbon::parse($existing->start_date)
        : null;
    if (! \App\Support\LeaveWorkdayRules::canApplyStartingOn($startDate, null, $existingStart, (string) $leaveType)) {
        return redirect()->back()
            ->withErrors(['start_date' => \App\Support\LeaveWorkdayRules::sameDayApplyBlockedMessage((string) $leaveType)])
            ->withInput();
    }

    $startYear = LeaveYear::forDate($startDate);
    $endYear = LeaveYear::forDate($endDate);
    $publicHolidays = $this->generateHolidaysRange((int) $startDate->year, (int) $endDate->year);
    $requestedByYear = $this->workingDaysByYear($startDate, $endDate, $publicHolidays, $halfDay);

    $policies = LeavePolicy::all()->keyBy('leave_type');
    $annualKey = $policies->has('Annual') ? 'Annual' : ($policies->has('Annual Leave') ? 'Annual Leave' : 'Annual');
    $annualDefault = (float) ($policies[$annualKey]->default_days ?? 30);
    $typeDefault = (float) ($policies[$leaveType]->default_days ?? 0);
    $isUnpaid = strcasecmp((string) $leaveType, 'Unpaid') === 0;

    foreach ($requestedByYear as $year => $daysInYear) {
        if ($daysInYear <= 0 || $isUnpaid) {
            continue;
        }

        $yearLabel = LeaveYear::label((int) $year);
        [$yearStart, $yearEnd] = LeaveYear::window((int) $year);
        $yearHolidays = $this->generateHolidaysRange((int) $yearStart->year, (int) $yearEnd->year);

        if ($daysInYear > $typeDefault) {
            return redirect()->back()
                ->withErrors([
                    'date' => "You cannot apply for more than {$typeDefault} days of {$leaveType} leave in {$yearLabel}. This request uses {$daysInYear} working day(s) in {$yearLabel}.",
                ])
                ->withInput();
        }

        $remainingForType = $this->remainingDaysInYear(
            (int) $userId,
            (string) $leaveType,
            (int) $year,
            $typeDefault,
            $yearHolidays
        );

        if ($daysInYear > $remainingForType) {
            return redirect()->back()
                ->withErrors([
                    'balance' => "Your available {$leaveType} balance for {$yearLabel} is {$remainingForType} day(s). This request uses {$daysInYear} working day(s) in {$yearLabel} (excluding weekends and public holidays).",
                ])
                ->withInput();
        }

        if (AnnualLeaveRules::countsAgainstAnnual((string) $leaveType)) {
            $remainingAnnual = $this->remainingDaysInYear(
                (int) $userId,
                (string) $leaveType,
                (int) $year,
                $annualDefault,
                $yearHolidays,
                true
            );

            if ($remainingAnnual <= 0) {
                return redirect()->back()
                    ->withErrors(['balance' => AnnualLeaveRules::exhaustedAnnualMessage().' ('.$yearLabel.')'])
                    ->withInput();
            }

            if ($daysInYear > $remainingAnnual) {
                return redirect()->back()
                    ->withErrors([
                        'balance' => "Your remaining annual leave balance for {$yearLabel} is {$remainingAnnual} day(s). This request uses {$daysInYear} working day(s) in {$yearLabel} (excluding weekends and public holidays).",
                    ])
                    ->withInput();
            }
        }
    }

    $overlapExists = LeaveRequest::where('user_id', $userId)
        ->when($existing, fn ($query) => $query->where('id', '!=', $existing->id))
        ->where(function ($query) {
            $query->whereRaw('LOWER(TRIM(status)) in (?, ?, ?)', ['pending', 'pending_senior', 'approved']);
        })
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

    if ($request->hasFile('file_uploads')) {
        if ($existing) {
            \App\Support\LeaveAttachmentLinks::deleteStoredFiles($existing);
        }
        \App\Support\LeaveAttachmentLinks::ensureStorageReady();
        $paths = [];
        foreach ($request->file('file_uploads') as $file) {
            $paths[] = $file->store('leave_files', 'public');
        }
        $validated['file_uploads'] = json_encode($paths);
    } elseif ($existing) {
        $validated['file_uploads'] = $existing->file_uploads;
    } else {
        $validated['file_uploads'] = null;
    }

    unset($validated['half_day']);
    $validated['is_half_day'] = $halfDay;
    if (! $halfDay) {
        $validated['half_day_period'] = null;
    }

    return $validated;
}

private function notifyReviewersOfRetroactiveApproval(LeaveRequest $leaveRequest): void
{
    $recipients = collect();
    $applicant = $leaveRequest->user;

    if (! $leaveRequest->isManagerApplicant()) {
        $assignedManager = $applicant?->manager_id
            ? User::query()->managers()->whereKey($applicant->manager_id)->where('is_active', true)->first()
            : null;
        if ($assignedManager) {
            $recipients->push($assignedManager);
        }
    }

    $assignedSenior = \App\Support\LeaveApprovalWorkflow::assignedSeniorForUser($applicant);
    if ($assignedSenior) {
        $recipients->push($assignedSenior);
    }

    $recipients = $recipients->merge(
        \App\Support\LeaveApprovalWorkflow::seniorsForLeave($leaveRequest)
    );

    foreach ($recipients->unique('id')->values() as $recipient) {
        try {
            $recipient->notify(new \App\Notifications\LeaveStatusNotification($leaveRequest, $applicant ?? $recipient));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

private function notifyReviewersOfUpdate(LeaveRequest $leaveRequest, bool $sentBackToManager): void
{
    $recipients = collect();

    if ($sentBackToManager || strtolower(trim((string) $leaveRequest->status)) === 'pending') {
        $applicant = $leaveRequest->user;
        $assignedManager = $applicant?->manager_id
            ? User::query()->managers()->whereKey($applicant->manager_id)->first()
            : null;
        $managers = $assignedManager
            ? collect([$assignedManager])
            : User::query()->where('role', 'manager')->get()->unique('email')->values();
        $recipients = $recipients->concat($managers);
    }

    $assignedSenior = \App\Support\LeaveApprovalWorkflow::assignedSeniorForUser($leaveRequest->user);
    if ($assignedSenior && strtolower(trim((string) $leaveRequest->status)) === 'pending') {
        $recipients = $recipients->push($assignedSenior);
    }

    if (strtolower(trim((string) $leaveRequest->status)) === 'pending_senior') {
        $recipients = $recipients->concat(\App\Support\LeaveApprovalWorkflow::seniorsForLeave($leaveRequest));
    }

    $recipients = $recipients
        ->unique('id')
        ->reject(fn (User $recipient) => (int) $recipient->id === (int) Auth::id())
        ->values();

    if ($recipients->isEmpty()) {
        return;
    }

    foreach ($recipients as $recipient) {
        try {
            $recipient->notifyNow(new LeaveRequestUpdatedNotification($leaveRequest, $sentBackToManager), ['database']);
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $recipient->notifyNow(new LeaveRequestUpdatedNotification($leaveRequest, $sentBackToManager), ['mail']);
        } catch (\Throwable $e) {
            report($e);
        }
    }
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

    \App\Support\LeaveAttachmentLinks::ensureStorageReady();
    $paths = [];
    foreach ($request->file('file_uploads') as $file) {
        $paths[] = $file->store('leave_files', 'public');
    }

    $leaveRequest->file_uploads = json_encode($paths);
    $leaveRequest->save();

    $leaveRequest->load('user');
    $recipients = collect();
    $applicant = $leaveRequest->user;
    if ($applicant && $applicant->manager_id) {
        $manager = User::query()->managers()->whereKey($applicant->manager_id)->first();
        if ($manager && (int) $manager->id !== (int) Auth::id()) {
            $recipients->push($manager);
        }
    }

    $assignedSenior = \App\Support\LeaveApprovalWorkflow::assignedSeniorForUser($applicant);
    if ($assignedSenior) {
        $recipients->push($assignedSenior);
    }

    $recipients = $recipients
        ->concat(\App\Support\LeaveApprovalWorkflow::seniorsForLeave($leaveRequest))
        ->unique('id')
        ->reject(fn (User $recipient) => (int) $recipient->id === (int) Auth::id())
        ->values();

    $notification = new ProofUploadedNotification($leaveRequest);
    foreach ($recipients as $recipient) {
        try {
            $recipient->notifyNow($notification, ['database']);
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $recipient->notifyNow($notification, ['mail']);
        } catch (\Throwable $e) {
            report($e);
        }
    }

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
    $publicHolidays = PublicHolidayService::datesForYearRange($startYear, $endYear);

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
    try {
        OverdueLeaveNotifier::notifyDue();
    } catch (\Throwable $e) {
        report($e);
    }
    MissingProofUnpaidConverter::convertDue();
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
        $query->overdue();
    } elseif ($status === 'pending') {
        $query->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])
            ->whereDate('start_date', '>=', Carbon::today());
    } elseif ($status !== 'all') {
        $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
    }

    $effectiveSearch = $search;
    if ($status === 'all' && $searchKey === 'overdue') {
        $query->overdue();
        $effectiveSearch = '';
    } elseif ($status === 'all' && $searchKey === 'pending') {
        $query->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])
            ->whereDate('start_date', '>=', Carbon::today());
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
        return redirect()->route($this->myRequestsRoute())
            ->with('error', \App\Support\LeaveWorkdayRules::withdrawBlockedMessage($leaveRequest));
    }

    $previousStatus = strtolower(trim((string) $leaveRequest->status));
    $wasManagerApplicant = $leaveRequest->isManagerApplicant();
    $leaveRequest->status = 'withdrawn';
    $leaveRequest->save();

    $recipients = collect();
    $applicant = $leaveRequest->user;

    if (! $wasManagerApplicant) {
        $managerId = $leaveRequest->recommended_by ?: ($applicant->manager_id ?? null);
        $assignedManager = $managerId
            ? User::query()->managers()->whereKey($managerId)->where('is_active', true)->first()
            : null;

        if ($assignedManager) {
            $recipients->push($assignedManager);
        }
    }

    $assignedSenior = \App\Support\LeaveApprovalWorkflow::assignedSeniorForUser($applicant);
    if ($assignedSenior) {
        $recipients->push($assignedSenior);
    }

    $recipients = $recipients->merge(
        \App\Support\LeaveApprovalWorkflow::seniorsForLeave($leaveRequest)
    );

    foreach ($recipients->unique('id')->values() as $recipient) {
        try {
            $recipient->notify(new LeaveWithdrawnNotification($leaveRequest, $previousStatus));
        } catch (\Throwable $e) {
            report($e);
        }

        if (! empty($recipient->email)) {
            try {
                Mail::to($recipient->email)->send(
                    new \App\Mail\LeaveWithdrawalMail(
                        $leaveRequest,
                        $previousStatus,
                        \App\Support\EmailGreeting::displayName($recipient),
                        LeavePortalLinks::reviewUrlFor($recipient, $leaveRequest->id)
                    )
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    $notice = $previousStatus === 'approved'
        ? ($wasManagerApplicant
            ? 'Approved leave withdrawn. Senior managers were notified, and your leave balance was restored.'
            : 'Approved leave withdrawn. Your manager and senior manager were notified, and your leave balance was restored.')
        : ($wasManagerApplicant
            ? 'Leave request withdrawn and senior managers notified.'
            : 'Leave request withdrawn. Your manager and senior manager were notified.');

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
    $extension = strtolower(pathinfo($downloadName, PATHINFO_EXTENSION));
    $mime = $disk->mimeType($path) ?: null;
    if (! is_string($mime) || $mime === '' || $mime === 'application/octet-stream') {
        $mime = \App\Support\LeaveAttachmentLinks::mimeForExtension($extension) ?: 'application/octet-stream';
    }
    $previewable = \App\Support\LeaveAttachmentLinks::isPreviewablePath($path);
    $forceDownload = $request->boolean('download');

    if ($forceDownload || ! $previewable) {
        return response()->download($fullPath, $downloadName, ['Content-Type' => $mime]);
    }

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $downloadName).'"',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'private, max-age=60',
    ]);
}

}
