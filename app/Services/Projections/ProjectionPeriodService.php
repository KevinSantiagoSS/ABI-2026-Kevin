<?php

namespace App\Services\Projections;

use App\Models\AcademicPeriod;
use App\Models\ResearchStaff\ResearchStaffAcademicPeriod;
use Illuminate\Support\Collection;

class ProjectionPeriodService
{
    public function activePeriod(): ?ResearchStaffAcademicPeriod
    {
        return ResearchStaffAcademicPeriod::query()
            ->where('is_active', true)
            ->where('status', AcademicPeriod::STATUS_ACTIVE)
            ->orderByDesc('start_date')
            ->first();
    }

    public function targetPeriod(?ResearchStaffAcademicPeriod $activePeriod = null): ?ResearchStaffAcademicPeriod
    {
        $activePeriod ??= $this->activePeriod();

        if (! $activePeriod || ! $activePeriod->start_date) {
            return null;
        }

        return ResearchStaffAcademicPeriod::query()
            ->where('id', '!=', $activePeriod->id)
            ->whereDate('start_date', '>', $activePeriod->start_date)
            ->orderBy('start_date')
            ->first();
    }

    public function targetPeriodOrFail(): ResearchStaffAcademicPeriod
    {
        $targetPeriod = $this->targetPeriod();

        abort_unless(
            $targetPeriod,
            403,
            'Debes configurar un periodo academico activo y el siguiente periodo academico antes de usar Proyecciones.'
        );

        return $targetPeriod;
    }

    public function allPeriods(): Collection
    {
        return ResearchStaffAcademicPeriod::query()
            ->orderByDesc('start_date')
            ->get();
    }

    public function isCurrentTarget(?int $academicPeriodId): bool
    {
        $targetPeriod = $this->targetPeriod();

        return $targetPeriod !== null && (int) $targetPeriod->id === (int) $academicPeriodId;
    }
}
