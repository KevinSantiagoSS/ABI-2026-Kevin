<?php

namespace App\Helpers;

use App\Models\User;

class UserRoleHelper
{
    public static function displayName(?User $user, $details = null): string
    {
        $role = self::normalizeRole($user?->role);
        $details = $details ?? self::resolveDetails($user, $role);

        return match ($role) {
            'student' => self::studentLabel($details),
            'professor' => 'Profesor',
            'committee_leader' => 'Lider de comite',
            'research_staff' => 'Personal de investigacion',
            default => self::fallbackLabel($user?->role),
        };
    }

    public static function badgeClass(?string $role): string
    {
        return match (self::normalizeRole($role)) {
            'student' => 'bg-info-lt',
            'professor' => 'bg-primary-lt',
            'committee_leader' => 'bg-warning-lt',
            'research_staff' => 'bg-success-lt',
            default => 'bg-secondary-lt',
        };
    }

    private static function studentLabel($details): string
    {
        $programName = data_get($details, 'cityProgram.program.name');

        return $programName
            ? 'Estudiante de ' . $programName
            : 'Estudiante';
    }

    private static function resolveDetails(?User $user, string $role)
    {
        if (! $user) {
            return null;
        }

        return match ($role) {
            'student' => $user->student,
            'professor', 'committee_leader' => $user->professor,
            'research_staff' => $user->researchStaff,
            default => null,
        };
    }

    private static function normalizeRole(?string $role): string
    {
        return match ((string) $role) {
            'committe_leader' => 'committee_leader',
            default => (string) $role,
        };
    }

    private static function fallbackLabel(?string $role): string
    {
        $normalizedRole = self::normalizeRole($role);

        if ($normalizedRole === '') {
            return 'Usuario';
        }

        return ucfirst(str_replace('_', ' ', $normalizedRole));
    }
}
