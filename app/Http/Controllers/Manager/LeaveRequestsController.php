<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = LeaveRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->where('status', '!=', 'Withdrawn'); // EXCLUDE withdrawn always

        if ($status) {
            $query->where('status', ucfirst(strtolower($status)));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('leave_type', 'like', '%' . $search . '%')
                  ->orWhere('status', 'like', '%' . $search . '%')
                  ->orWhere('start_date', 'like', '%' . $search . '%')
                  ->orWhere('end_date', 'like', '%' . $search . '%')
                  ->orWhere('important_comment', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', '%' . $search . '%')
                         ->orWhere('last_name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%')
                         ->orWhere('department', 'like', '%' . $search . '%')
                         ->orWhere('designation', 'like', '%' . $search . '%');
                  });

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $requests = $query->paginate(10);

        return view('manager.request.index', compact('requests', 'status'));
    }
}
