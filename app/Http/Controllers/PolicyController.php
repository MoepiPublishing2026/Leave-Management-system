<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\LeavePolicy;

class PolicyController extends Controller
{
    public function show()
    {
        $policies = LeavePolicy::all()->keyBy('leave_type');
        $leaveTypes = ['Annual', 'Sick', 'Maternity', 'Paternity', 'Bereavement', 'Unpaid', 'Family Responsibility', 'Study'];

        return view('policies', compact('policies', 'leaveTypes'));
    }

    public function downloadPdf()
    {
        $policies = LeavePolicy::all()->keyBy('leave_type');
        $leaveTypes = ['Annual', 'Sick', 'Maternity', 'Paternity', 'Bereavement', 'Unpaid', 'Family Responsibility', 'Study'];

        $pdf = Pdf::loadView('policies_pdf', compact('policies', 'leaveTypes'));
        return $pdf->download('company_leave_policies.pdf');
    }
}
