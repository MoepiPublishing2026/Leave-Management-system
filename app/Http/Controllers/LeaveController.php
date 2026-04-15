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

        // Monthly bars
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $barData = [];
        foreach ($barLabels as $month) {
            $barData[] = LeaveRequest::where('user_id', $userId)
                ->whereMonth('created_at', date('m', strtotime($month . ' 1')))
                ->count();
        }

        // Policies keyed by leave_type
        $policies = LeavePolicy::all()->keyBy('leave_type');

        $year = Carbon::now()->year;
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

        return view('dashboard', compact(
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'withdrawnCount',
            'overduePendingCount',
            'leaveTypeCounts',
            'barLabels',
            'barData',
            'leaveBalances'
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


public function myRequests()
{
    $userId = Auth::id();

    $publicHolidays = [];
    // Fetch all leaves of this user
    $leaveRequests = LeaveRequest::where('user_id', $userId)
        ->orderBy('start_date', 'desc')
        ->get();

    // Extract unique years from all leaves
    $years = $leaveRequests->flatMap(function ($leave) {
        return [Carbon::parse($leave->start_date)->year, Carbon::parse($leave->end_date)->year];
    })->unique()->values()->all();

    // Get all public holidays for years implicated
    foreach ($years as $year) {
        $holidays = Yasumi::create('SouthAfrica', $year);
        foreach ($holidays as $holiday) {
            $publicHolidays[] = $holiday->format('Y-m-d');
        }
    }

    // Calculate working days excluding weekend and public holidays per leave
    $leaveRequests->transform(function($leave) use ($publicHolidays) {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $current = $start->copy();
        $daysCount = 0;
        while ($current->lte($end)) {
            if (!$current->isWeekend() && !in_array($current->toDateString(), $publicHolidays)) {
                $daysCount++;
            }
            $current->addDay();
        }
        if (!empty($leave->is_half_day) && $daysCount > 0) {
            $daysCount = 0.5;
        }
        $leave->days = $daysCount;
        return $leave;
    });

    return view('leave.my_requests', compact('leaveRequests'));
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
    $userId = auth()->id();
    $statusKey = strtolower(trim((string) $status));
    $query = LeaveRequest::where('user_id', $userId);

    if ($statusKey === 'overdue') {
        $query->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today());
    } elseif ($statusKey === 'pending') {
        $query->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '>=', Carbon::today());
    } else {
        $query->whereRaw('LOWER(status) = ?', [$statusKey]);
    }

    $leaves = $query->get();

    return view('leave.leaves_by_status', compact('leaves', 'status'));
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


}
