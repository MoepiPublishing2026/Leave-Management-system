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

        if ($role === 'manager') {
            $query->employees()->where('manager_id', $viewer->id);
        } elseif ($role === 'admin') {
            $query->whereIn('role', ['employee', 'manager']);
        } else {
            $query->employees();
        }

        return $query->get(['id', 'first_name', 'last_name', 'role']);
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
        if ($employeeId === null) {
            return;
        }

        $query->where($column, $employeeId);
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
