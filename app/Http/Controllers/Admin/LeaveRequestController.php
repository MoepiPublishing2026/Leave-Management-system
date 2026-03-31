<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));
        $searchKey = strtolower($search);

        $query = LeaveRequest::with(['user', 'overdueEscalatedBy', 'handledBy'])
            ->orderByDesc('created_at');

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

        $requests = $query->paginate(10)->withQueryString();

        return view('admin.requests.index', compact('requests', 'status', 'search'));
    }

}
