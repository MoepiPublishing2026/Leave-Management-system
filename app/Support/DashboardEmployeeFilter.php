<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardEmployeeFilter
{
    public static function employees(?User $viewer = null): Collection
    {
        $viewer = $viewer ?? Auth::user();
        $role = strtolower((string) ($viewer->role ?? ''));

        $query = User::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($role === 'manager' || $role === 'admin') {
            $ids = self::allowedUserIds($viewer);
            if ($ids->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $ids);
            }
        } else {
            $query->employees();
        }

        return $query->get(['id', 'first_name', 'last_name', 'role']);
    }

    /**
     * People this viewer is allowed to see: a manager's assigned employees,
     * or a senior manager's reporting line. Never the whole company.
     */
    public static function allowedUserIds(?User $viewer = null): Collection
    {
        $viewer = $viewer ?? Auth::user();
        $role = strtolower((string) ($viewer->role ?? ''));

        if ($role === 'manager') {
            return ManagerTeam::employeeIdsFor($viewer);
        }

        if ($role === 'admin') {
            return LeaveApprovalWorkflow::reportingLineUserIds($viewer);
        }

        return collect();
    }

    public static function selectedId(mixed $value): ?int
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    public static function apply(Builder $query, ?int $employeeId, string $column = 'user_id'): void
    {
        $viewer = Auth::user();
        $role = strtolower((string) ($viewer->role ?? ''));
        $restrictToAssigned = in_array($role, ['manager', 'admin'], true);
        $ids = $restrictToAssigned ? self::allowedUserIds($viewer) : collect();

        if ($employeeId !== null) {
            if ($restrictToAssigned && ! $ids->contains($employeeId)) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where($column, $employeeId);

            return;
        }

        if (! $restrictToAssigned) {
            return;
        }

        if ($ids->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn($column, $ids->all());
    }

    public static function applyToUsers(Builder $query, ?User $viewer = null): void
    {
        $viewer = $viewer ?? Auth::user();
        $role = strtolower((string) ($viewer->role ?? ''));
        if (! in_array($role, ['manager', 'admin'], true)) {
            return;
        }

        $ids = self::allowedUserIds($viewer);
        if ($ids->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('id', $ids->all());
    }

    /**
     * @return array<string, mixed>
     */
    public static function queryParams(string $monthFilter, ?int $employeeId): array
    {
        $params = ['month' => $monthFilter];

        if ($employeeId !== null) {
            $params['employee'] = $employeeId;
        }

        return $params;
    }
}
