<?php

namespace App\Http\Controllers;

use App\Models\LeavePolicy;
use App\Support\PdfDocuments;
use Illuminate\Http\Request;

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

        $pdf = PdfDocuments::make('policies_pdf', compact('policies', 'leaveTypes'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('company_leave_policies.pdf');
    }
}
