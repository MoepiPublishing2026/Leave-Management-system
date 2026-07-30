<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use Illuminate\Http\Request;

class LeaveSettingsController extends Controller
{
    public function index()
    {
        $leaveTypes = ['Annual','Sick','Maternity','Paternity','Bereavement','Unpaid','Family Responsibility','Study'];
        $policies = LeavePolicy::all()->keyBy('leave_type');
        return view('admin.settings', compact('leaveTypes', 'policies'));
    }

    public function save(Request $request)
{
    $leaveTypes = $request->input('leave_type', []); // default to empty array to prevent foreach null error

    foreach ($leaveTypes as $type) {
        LeavePolicy::updateOrCreate(
            ['leave_type' => $type],
            [
                'default_days' => $request->input("default_days.$type", 0),
                'max_carry_over' => $request->input("max_carry_over.$type", 0),
                'allow_half_days' => $request->has("allow_half_days.$type"),
                'requires_proof' => $request->has("requires_proof.$type"),
            ]
        );
    }

    return back()->with('saved', 1);
}

}
