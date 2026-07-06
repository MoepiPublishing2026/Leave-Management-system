<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SoftwareController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\LicensesController;
use App\Http\Controllers\ViewerDashboardController;
use App\Http\Controllers\ViewerLicenseController;
use App\Http\Controllers\Viewer\ViewerController;

use App\Http\Controllers\Employee\CalendaController;


use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveSettingsController;

use App\Http\Controllers\Admin\LeaveRequestController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ManagerLeaveRequestController;

//Manager Controllers
use App\Http\Controllers\Manager\DashboardsController;
use App\Http\Controllers\Manager\RequestsController;
use App\Http\Controllers\Manager\AnalyticController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\CalendarsController;
use App\Http\Controllers\Manager\LeaveRequestsController;


// Public welcome route
Route::get('/', function () {
    return view('welcome');
});

Route::get('/signup', [SignupController::class, 'showSignupForm'])->name('signup');
Route::post('/signup', [SignupController::class, 'signup'])->name('signup.post');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/verify-2fa', [AuthController::class, 'show2faForm'])->name('login.verify');
Route::post('/verify-2fa', [AuthController::class, 'verify2fa'])->name('login.verify.post');

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::get('/policies', [PolicyController::class, 'show'])->name('policies');
    Route::get('/policies/download', [PolicyController::class, 'downloadPdf'])->name('policies.download');

Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');



// calendar main page


Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('calendar', [CalendaController::class, 'index'])->name('calendar');
});


// show leaves on clicked day
Route::get('admin/leaves/{year}/{month}/{day}', function($year, $month, $day) {
    $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
    $leaves = \App\Models\LeaveRequest::whereDate('start_date', '<=', $date)
        ->whereDate('end_date', '>=', $date)
        ->with('user')->get();
    return view('admin.leaves.byday', compact('leaves', 'date'));
})->name('admin.leaves.byday');


Route::get('/dashboard', [LeaveController::class, 'dashboard'])
    ->middleware('auth')->name('dashboard');

// Leave application page
Route::get('/apply', [LeaveController::class, 'applyForm'])
    ->middleware('auth')->name('apply');

// Handle leave application form submit
Route::post('/apply-for-leave', [LeaveController::class, 'submit'])
    ->middleware('auth')->name('leave.submit');

Route::get('/leave/my-requests', [LeaveController::class, 'myRequests'])->middleware('auth')->name('leave.myRequests');
Route::post('/leave/requests/{id}/proof', [LeaveController::class, 'uploadProof'])->middleware('auth')->name('leave.uploadProof');
Route::patch('/leave/withdraw/{id}', [LeaveController::class, 'withdraw'])->middleware('auth')->name('leave.withdraw');
Route::delete('/leave/requests/{id}', [LeaveController::class, 'destroy'])->name('leave.destroy')->middleware('auth');
Route::get('/leave/balance', [LeaveController::class, 'leaveBalance'])->name('leave.balance')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [AuthController::class, 'update'])->name('profile.update');
    Route::delete('/profile/picture', [AuthController::class, 'destroyProfilePicture'])->name('profile.picture.destroy');
    Route::get('/settings', fn () => redirect()->route('profile.edit'))->name('settings.edit');
    Route::post('/settings', [AuthController::class, 'update'])->name('settings.update');
});

Route::get('/leave/status/{status}', [LeaveController::class, 'statusLeaves'])->middleware('auth')->name('leave.status');

Route::get('/reports/export', [ReportsController::class, 'exportCsv'])->name('admin.reports.export_csv');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

   Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
   Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('reports.export_pdf');

    Route::get('/requests', [LeaveRequestController::class, 'index'])->name('requests');
    Route::delete('/requests/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('requests.destroy');
    Route::get('/manager-requests', [ManagerLeaveRequestController::class, 'index'])->name('manager-requests');
    Route::patch('/manager-requests/{id}/approve', [ManagerLeaveRequestController::class, 'approve'])->name('manager-requests.approve');
    Route::patch('/manager-requests/{id}/reject', [ManagerLeaveRequestController::class, 'reject'])->name('manager-requests.reject');
    Route::get('/manager-requests/{id}/show-reason', [ManagerLeaveRequestController::class, 'showRejectionReason'])->name('manager-requests.show_reason');
   Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
   Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/settings', [\App\Http\Controllers\Admin\LeaveSettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\LeaveSettingsController::class, 'save'])->name('settings.save');

 Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
   
     Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

 // route in web.php


    // Add other admin routes here as needed.
});
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.post');
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    });
});

//Manager Routes
Route::prefix('manager')->name('manager.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardsController::class, 'team'])->name('dashboard');
    Route::get('/my-leave-dashboard', [DashboardsController::class, 'personal'])->name('personal');
    Route::get('/team', fn () => redirect()->route('manager.dashboard'))->name('team');

Route::get('requests', [RequestsController::class, 'index'])->name('requests.index');
    Route::patch('requests/{id}/approve', [RequestsController::class, 'approve'])->name('requests.approve');
    Route::patch('requests/{id}/reject', [RequestsController::class, 'reject'])->name('requests.reject');
    Route::post('requests/{id}/escalate', [RequestsController::class, 'escalate'])->name('requests.escalate');
    Route::get('requests/{id}/show-reason', [RequestsController::class, 'showRejectionReason'])->name('requests.show_reason');
    Route::delete('requests/{leaveRequest}', [RequestsController::class, 'destroy'])->name('requests.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
    Route::get('/analytics', [AnalyticController::class, 'index'])->name('analytics');
    Route::get('/calendar', [CalendarsController::class, 'index'])->name('calendar');
    Route::get('/my-calendar', [CalendarsController::class, 'personal'])->name('myCalendar');
    Route::get('/apply', [LeaveController::class, 'applyForm'])->name('apply');
    Route::post('/apply', [LeaveController::class, 'submit'])->name('leave.submit');
    Route::get('/my-leave', [LeaveController::class, 'myRequests'])->name('myLeave');
    Route::get('/request', function () {
        return redirect()->route('manager.requests.index', ['status' => 'all']);
    })->name('request');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/messages', [MessagesController::class, 'index'])->name('messages.index');
    Route::get('/messages/leave/{leaveRequest}', [MessagesController::class, 'openLeaveRequest'])->name('messages.leave');
    Route::post('/messages/{conversation}/send', [MessagesController::class, 'send'])->name('messages.send');
    Route::get('/messages/voice/{message}', [MessagesController::class, 'voice'])->name('messages.voice');
    Route::delete('/messages/{message}', [MessagesController::class, 'destroy'])->name('messages.destroy');
    Route::post('/messages/{message}/forward', [MessagesController::class, 'forward'])->name('messages.forward');

    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationsController::class, 'open'])->name('notifications.open');
    // optionally add route to mark notifications as read
    Route::post('/notifications/mark-read', [NotificationsController::class, 'markAsRead'])->name('notifications.mark.read');

    Route::get('/users/{user}/profile-picture', [AuthController::class, 'profilePicture'])->name('users.profile_picture');
    Route::get('/leave-requests/{leaveRequest}/attachments/{attachment}', [LeaveController::class, 'showAttachment'])
        ->name('leave.attachments.show');
});
