<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use Illuminate\Http\Request;

class LeaveSettingsController extends Controller
{
    private function assertCanManagePolicies(): void
    {
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));
        if (! in_array($role, ['admin', 'manager'], true)) {
            abort(403, 'Only managers and senior managers can change leave policies.');
        }
    }

    private function settingsContext(): array
    {
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));
        $isManager = $role === 'manager';

        return [
            'layout' => $isManager ? 'layouts.manager' : 'layouts.admin',
            'formRoute' => $isManager ? 'manager.settings.save' : 'admin.settings.save',
            'pageTitle' => 'Leave Policies',
        ];
    }

    public function index()
    {
        $this->assertCanManagePolicies();

        $leaveTypes = ['Annual', 'Sick', 'Maternity', 'Paternity', 'Bereavement', 'Unpaid', 'Family Responsibility', 'Study'];
        $policies = LeavePolicy::all()->keyBy('leave_type');
        $context = $this->settingsContext();

        return view('admin.settings', array_merge(compact('leaveTypes', 'policies'), $context));
    }

    public function save(Request $request)
    {
        $this->assertCanManagePolicies();

        $leaveTypes = $request->input('leave_type', []);

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

        return back()->with('success', 'Leave policies saved successfully.');
    }
}
