<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Mail\LeaveResponseMail;
use Illuminate\Support\Facades\Mail;
use App\Notifications\LeaveStatusNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Notifications\OverdueLeaveEscalatedNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;



class RequestsController extends Controller
{
    private function ensureHandledColumnsExist(): void
    {
        if (Schema::hasColumn('leave_requests', 'handled_by')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->string('handled_action', 40)->nullable();
        });
    }

    public function index(Request $request)
    {
        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));
        $searchKey = strtolower($search);
        $monthFilter = $request->query('month');
        $query = LeaveRequest::with('user');

        if (is_string($monthFilter) && $monthFilter !== '') {
            $now = Carbon::now();
            if (ctype_digit((string) $monthFilter)) {
                $query->whereYear('start_date', (int) $monthFilter);
            } elseif ($monthFilter === 'this_month') {
                $query->whereYear('start_date', $now->year)
                    ->whereMonth('start_date', $now->month);
            } elseif ($monthFilter === 'this_quarter') {
                $quarter = (int) ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $startMonth + 2;
                $query->whereYear('start_date', $now->year)
                    ->whereBetween(\DB::raw('MONTH(start_date)'), [$startMonth, $endMonth]);
            } elseif ($monthFilter === 'this_year') {
                $query->whereYear('start_date', $now->year);
            }
        }

        if ($status === 'overdue') {
            $query->whereRaw('LOWER(status) = ?', ['pending'])
                ->whereDate('start_date', '<', Carbon::today());
        } elseif ($status === 'pending') {
            $query->whereRaw('LOWER(status) = ?', ['pending'])
                ->whereDate('start_date', '>=', Carbon::today());
        } elseif ($status !== 'all') {
            $query->whereRaw('LOWER(status) = ?', [$status]);
        } else {
            $query->whereRaw('LOWER(status) != ?', ['withdrawn']);
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
                    ->orWhere('important_comment', 'like', '%' . $effectiveSearch . '%')
                    ->orWhereHas('user', function ($q2) use ($effectiveSearch) {
                        $q2->where('first_name', 'like', '%' . $effectiveSearch . '%')
                            ->orWhere('last_name', 'like', '%' . $effectiveSearch . '%')
                            ->orWhere('email', 'like', '%' . $effectiveSearch . '%')
                            ->orWhere('department', 'like', '%' . $effectiveSearch . '%')
                            ->orWhere('designation', 'like', '%' . $effectiveSearch . '%');
                    });

                if (ctype_digit($effectiveSearch)) {
                    $q->orWhere('id', (int) $effectiveSearch);
                }
            });
        }

        $requests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        return view('manager.requests.index', compact('requests', 'status', 'search', 'monthFilter'));
    }

  


public function approve($id)
{
    $leave = LeaveRequest::findOrFail($id);

    if (strtolower($leave->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only pending requests can be approved.');
    }

    if ($leave->isOverdue()) {
        return redirect()->back()->with('error', 'Overdue requests cannot be approved. Submit a late response reason instead.');
    }

    $this->ensureHandledColumnsExist();
    $leave->status = 'approved';
    $leave->handled_by = Auth::id();
    $leave->handled_at = Carbon::now();
    $leave->handled_action = 'approved';
    $leave->save();

    $conversation = LeaveRequestConversation::firstOrCreate([
        'leave_request_id' => $leave->id,
        'manager_id' => Auth::id(),
    ]);
    LeaveRequestMessage::create([
        'conversation_id' => $conversation->id,
        'sender_id' => Auth::id(),
        'body' => 'Decision: Approved.',
    ]);
    $conversation->touch();

    // Notify user in-app (database)
    $manager = Auth::user();
    try {
        $leave->user->notify(new LeaveStatusNotification($leave, $manager));
    } catch (\Throwable $e) {
        report($e);
    }

    // Send email, but don't fail the approval if mail is misconfigured
    try {
        Mail::to($leave->user->email)->send(new LeaveResponseMail($leave));
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()->back()->with('success', 'Request approved and employee notified.');
}

public function reject(Request $r, $id)
{
    $leave = LeaveRequest::findOrFail($id);

    if (strtolower($leave->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only pending requests can be rejected.');
    }

    if ($leave->isOverdue()) {
        return redirect()->back()->with('error', 'Overdue requests cannot be rejected. Submit a late response reason instead.');
    }

    $r->validate(['rejection_reason' => 'required|string|max:500']);

    $this->ensureHandledColumnsExist();
    $leave->status = 'rejected';
    $leave->rejection_reason = $r->input('rejection_reason');
    $leave->handled_by = Auth::id();
    $leave->handled_at = Carbon::now();
    $leave->handled_action = 'rejected';
    $leave->save();

    $conversation = LeaveRequestConversation::firstOrCreate([
        'leave_request_id' => $leave->id,
        'manager_id' => Auth::id(),
    ]);
    LeaveRequestMessage::create([
        'conversation_id' => $conversation->id,
        'sender_id' => Auth::id(),
        'body' => 'Decision: Rejected. Reason: ' . $leave->rejection_reason,
    ]);
    $conversation->touch();

    // Notify user in-app (database)
    $manager = Auth::user();
    try {
        $leave->user->notify(new LeaveStatusNotification($leave, $manager));
    } catch (\Throwable $e) {
        report($e);
    }

    // Send email, but don't fail the rejection if mail is misconfigured
    try {
        Mail::to($leave->user->email)->send(new LeaveResponseMail($leave));
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()->back()->with('success', 'Request rejected and employee notified.');
}

public function escalate(Request $r, $id)
{
    $leave = LeaveRequest::with('user')->findOrFail($id);

    if (!$leave->isOverdue()) {
        return redirect()->back()->with('error', 'Only overdue pending requests can be escalated.');
    }

    $r->validate(['overdue_escalation_reason' => 'required|string|max:1000']);

    if (!Schema::hasColumn('leave_requests', 'overdue_escalation_reason')) {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('overdue_escalation_reason')->nullable();
            $table->timestamp('overdue_escalated_at')->nullable();
            $table->unsignedBigInteger('overdue_escalated_by')->nullable();
        });
    }

    $leave->overdue_escalation_reason = $r->input('overdue_escalation_reason');
    $leave->overdue_escalated_at = Carbon::now();
    $leave->overdue_escalated_by = Auth::id();
    $this->ensureHandledColumnsExist();
    $leave->handled_by = Auth::id();
    $leave->handled_at = Carbon::now();
    $leave->handled_action = 'unattended';
    $leave->save();

    $conversation = LeaveRequestConversation::firstOrCreate([
        'leave_request_id' => $leave->id,
        'manager_id' => Auth::id(),
    ]);
    LeaveRequestMessage::create([
        'conversation_id' => $conversation->id,
        'sender_id' => Auth::id(),
        'body' => 'Unattended request. Late response reason: ' . $leave->overdue_escalation_reason,
    ]);
    $conversation->touch();

    $admins = User::where('role', 'admin')->get();
    $managerName = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''));
    if ($managerName === '') {
        $managerName = 'Manager';
    }
    foreach ($admins as $admin) {
        $admin->notify(new OverdueLeaveEscalatedNotification($leave, $managerName));
    }

    return redirect()->back()->with('success', 'Late response reason sent to admin.');
}


    public function showRejectionReason($id)
    {
        $request = LeaveRequest::findOrFail($id);
        if (strtolower($request->status) !== 'rejected') {
            return redirect()->route('manager.requests.index')->with('error', 'No rejection reason available.');
        }
        return view('manager.requests.rejection_reason', compact('request'));
    }


    
}
