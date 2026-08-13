<?php

namespace App\Support;

use App\Models\ManagerDepartment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentManagers
{
    public static function managerForDepartment(?string $department): ?User
    {
        $department = trim((string) $department);
        if ($department === '') {
            return null;
        }

        $row = ManagerDepartment::query()
            ->where('department', $department)
            ->with(['manager' => fn ($q) => $q->managers()->where('is_active', true)])
            ->first();

        $manager = $row?->manager;
        if (! $manager || strtolower((string) ($manager->role ?? '')) !== 'manager' || ! $manager->is_active) {
            return null;
        }

        return $manager;
    }

    public static function managerIdForDepartment(?string $department): ?int
    {
        return self::managerForDepartment($department)?->id;
    }

    /** @return list<string> */
    public static function departmentsForManager(User|int $manager): array
    {
        $managerId = $manager instanceof User ? (int) $manager->id : (int) $manager;

        return ManagerDepartment::query()
            ->where('user_id', $managerId)
            ->orderBy('department')
            ->pluck('department')
            ->map(fn ($d) => (string) $d)
            ->values()
            ->all();
    }

    /**
     * Departments already owned by another active manager.
     *
     * @param  list<string>  $departments
     * @return array<string, string> department => manager name
     */
    public static function conflicts(array $departments, ?int $exceptManagerId = null): array
    {
        $departments = array_values(array_unique(array_filter(array_map(
            fn ($d) => trim((string) $d),
            $departments
        ))));

        if ($departments === []) {
            return [];
        }

        $query = ManagerDepartment::query()
            ->whereIn('department', $departments)
            ->with('manager:id,first_name,last_name,role,is_active');

        if ($exceptManagerId) {
            $query->where('user_id', '!=', $exceptManagerId);
        }

        $conflicts = [];
        foreach ($query->get() as $row) {
            $manager = $row->manager;
            if (! $manager || strtolower((string) ($manager->role ?? '')) !== 'manager') {
                continue;
            }
            $name = trim(($manager->first_name ?? '').' '.($manager->last_name ?? '')) ?: 'Manager #'.$manager->id;
            $conflicts[(string) $row->department] = $name;
        }

        return $conflicts;
    }

    /**
     * Replace a manager's department ownership and re-link employees.
     *
     * @param  list<string>  $departments
     */
    public static function syncForManager(User $manager, array $departments): void
    {
        $departments = array_values(array_unique(array_filter(array_map(
            fn ($d) => trim((string) $d),
            $departments
        ))));

        DB::transaction(function () use ($manager, $departments): void {
            $previous = self::departmentsForManager($manager);

            ManagerDepartment::query()
                ->where('user_id', $manager->id)
                ->delete();

            foreach ($departments as $department) {
                ManagerDepartment::query()->updateOrCreate(
                    ['department' => $department],
                    ['user_id' => $manager->id]
                );
            }

            $released = array_values(array_diff($previous, $departments));
            if ($released !== []) {
                User::query()
                    ->employees()
                    ->where('manager_id', $manager->id)
                    ->whereIn('department', $released)
                    ->update(['manager_id' => null]);
            }

            if ($departments !== []) {
                User::query()
                    ->employees()
                    ->whereIn('department', $departments)
                    ->update(['manager_id' => $manager->id]);
            }
        });
    }

    public static function clearForManager(User|int $manager): void
    {
        $managerId = $manager instanceof User ? (int) $manager->id : (int) $manager;

        DB::transaction(function () use ($managerId): void {
            $departments = ManagerDepartment::query()
                ->where('user_id', $managerId)
                ->pluck('department')
                ->all();

            ManagerDepartment::query()->where('user_id', $managerId)->delete();

            if ($departments !== []) {
                User::query()
                    ->employees()
                    ->where('manager_id', $managerId)
                    ->whereIn('department', $departments)
                    ->update(['manager_id' => null]);
            }
        });
    }

    /** Map department => manager display name for UI hints. */
    public static function ownershipMap(): Collection
    {
        return ManagerDepartment::query()
            ->with(['manager' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'role', 'is_active')])
            ->orderBy('department')
            ->get()
            ->filter(fn ($row) => $row->manager && strtolower((string) $row->manager->role) === 'manager' && $row->manager->is_active)
            ->mapWithKeys(function ($row) {
                $name = trim(($row->manager->first_name ?? '').' '.($row->manager->last_name ?? '')) ?: 'Manager #'.$row->manager->id;

                return [(string) $row->department => [
                    'manager_id' => (int) $row->manager->id,
                    'manager_name' => $name,
                ]];
            });
    }
}
