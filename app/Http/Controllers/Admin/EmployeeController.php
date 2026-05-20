<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;

class EmployeeController extends Controller
{
    // Show list of employees
    public function index()
    {
        $employees = User::whereIn('role', ['employee', 'manager','admin'])->get();
        return view('admin.employees.index', compact('employees'));
    }

    // Show create employee form
    public function create()
    {
        return view('admin.employees.create');
    }

    // Store new employee
  public function store(Request $request)
{
    $request->validate([
        'first_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
        'last_name' => ['required', 'string', 'max:15', 'regex:/^[a-zA-Z]+$/'],
        'department' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z\s&\-\/]*$/'],
        'designation' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z][A-Za-z\s&\-\/]*$/'],
        'email' => 'required|email:rfc|max:255|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:employee,manager,admin',
    ], [
        'first_name.regex' => 'First name must only contain letters.',
        'last_name.regex' => 'Last name must only contain letters.',
        'department.regex' => 'Department must only contain letters (no numbers).',
        'designation.regex' => 'Designation must only contain letters (no numbers).',
    ]);

    try {
        $rawPassword = $request->password;

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'department' => $request->department,
            'designation' => $request->designation,
            'email' => $request->email,
            'password' => Hash::make($rawPassword),
            'role' => $request->role,
        ]);

        Mail::to($user->email)->send(new WelcomeUserMail($user, $rawPassword));

        return redirect()->route('admin.employees.index')->with('success', 'Employee added successfully.');
    }catch (\Exception $e) {
    // Log the error for debugging
    Log::error($e->getMessage());

            // Pass the message into withErrors so you can see it in the view
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

}

    public function destroy($id)
    {
        $employee = User::findOrFail($id);

        if ((int) Auth::id() === (int) $employee->id) {
            return redirect()->route('admin.employees.index')->with('error', 'You cannot delete the currently logged-in user.');
        }

        DB::beginTransaction();
        try {
            // Null out foreign-key references where user acted as handler/escalator
            if (Schema::hasColumn('leave_requests', 'handled_by')) {
                LeaveRequest::where('handled_by', $employee->id)->update(['handled_by' => null]);
            }
            if (Schema::hasColumn('leave_requests', 'overdue_escalated_by')) {
                LeaveRequest::where('overdue_escalated_by', $employee->id)->update(['overdue_escalated_by' => null]);
            }

            // Remove conversations/messages where this user participated as manager/admin/sender
            if (Schema::hasTable('leave_request_conversations')) {
                $conversationQuery = LeaveRequestConversation::query();
                if (Schema::hasColumn('leave_request_conversations', 'manager_id')) {
                    $conversationQuery->orWhere('manager_id', $employee->id);
                }
                if (Schema::hasColumn('leave_request_conversations', 'admin_id')) {
                    $conversationQuery->orWhere('admin_id', $employee->id);
                }
                $conversationIdsForUserRole = $conversationQuery->pluck('id')->all();
                if (!empty($conversationIdsForUserRole)) {
                    LeaveRequestMessage::whereIn('conversation_id', $conversationIdsForUserRole)->delete();
                    LeaveRequestConversation::whereIn('id', $conversationIdsForUserRole)->delete();
                }
            }

            $leaveIds = LeaveRequest::where('user_id', $employee->id)->pluck('id')->all();
            if (!empty($leaveIds)) {
                $conversationIds = LeaveRequestConversation::whereIn('leave_request_id', $leaveIds)->pluck('id')->all();
                if (!empty($conversationIds)) {
                    LeaveRequestMessage::whereIn('conversation_id', $conversationIds)->delete();
                    LeaveRequestConversation::whereIn('id', $conversationIds)->delete();
                }
                LeaveRequest::whereIn('id', $leaveIds)->delete();
            }

            // Remove notifications linked to this user (if table exists)
            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $employee->id)
                    ->delete();
            }

            $employee->delete();
            DB::commit();

            return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('admin.employees.index')->with('error', 'Unable to delete this employee because related records could not be removed.');
        }
    }
}
