<?php

namespace App\Services\Projections;

use App\Models\ResearchStaff\ResearchStaffLoadProjection;

class LoadProjectionService
{
    public function __construct(
        private readonly ProjectionPeriodService $periods,
        private readonly StudentProjectionService $students,
    ) {
    }

    public function preview(?int $programId, ?int $projectedPg1Students, ?ResearchStaffLoadProjection $projection = null): array
    {
        $pg1Students = max((int) ($projectedPg1Students ?? 0), 0);
        $pg2Students = $this->resolveProjectedPg2Students($programId, $projection);

        return array_merge(
            [
                'projected_pg1_students' => $pg1Students,
                'projected_pg2_students' => $pg2Students,
                'pg2_source_label' => $projection && ! $this->periods->isCurrentTarget($projection->academic_period_id)
                    ? 'Valor historico conservado para este periodo.'
                    : 'Calculado automaticamente con estudiantes PG1 que ya tienen proyecto asignado en el periodo activo.',
            ],
            $this->calculateMetrics($pg1Students, $pg2Students)
        );
    }

    public function upsert(array $validated): array
    {
        $targetPeriod = $this->periods->targetPeriodOrFail();
        $programId = (int) $validated['program_id'];
        $pg1Students = (int) $validated['projected_pg1_students'];
        $pg2Students = $this->students->projectedPg2StudentsForProgram($programId);
        $metrics = $this->calculateMetrics($pg1Students, $pg2Students);

        $projection = ResearchStaffLoadProjection::query()
            ->firstOrNew([
                'academic_period_id' => $targetPeriod->id,
                'program_id' => $programId,
            ]);

        $created = ! $projection->exists;

        if ($created) {
            $projection->created_by_user_id = auth()->id();
        }

        $projection->fill(array_merge($metrics, [
            'academic_period_id' => $targetPeriod->id,
            'program_id' => $programId,
            'projected_pg1_students' => $pg1Students,
            'projected_pg2_students' => $pg2Students,
            'observations' => $validated['observations'] ?? null,
            'updated_by_user_id' => auth()->id(),
        ]));

        $projection->save();

        return [$projection, $created];
    }

    public function update(ResearchStaffLoadProjection $projection, array $validated): ResearchStaffLoadProjection
    {
        $programId = (int) $validated['program_id'];
        $pg1Students = (int) $validated['projected_pg1_students'];
        $pg2Students = $this->resolveProjectedPg2Students($programId, $projection);
        $metrics = $this->calculateMetrics($pg1Students, $pg2Students);

        $projection->fill(array_merge($metrics, [
            'program_id' => $programId,
            'projected_pg1_students' => $pg1Students,
            'projected_pg2_students' => $pg2Students,
            'observations' => $validated['observations'] ?? null,
            'updated_by_user_id' => auth()->id(),
        ]));

        $projection->save();

        return $projection;
    }

    public function calculateMetrics(int $pg1Students, int $pg2Students): array
    {
        $pg1Groups = (int) ceil($pg1Students / 3);
        $pg2Groups = (int) ceil($pg2Students / 3);
        $pg1WeeklyHours = $pg1Groups * 2;
        $pg2WeeklyHours = $pg2Groups * 2;

        return [
            'projected_pg1_groups' => $pg1Groups,
            'projected_pg2_groups' => $pg2Groups,
            'pg1_weekly_hours' => $pg1WeeklyHours,
            'pg2_weekly_hours' => $pg2WeeklyHours,
            'total_weekly_hours' => $pg1WeeklyHours + $pg2WeeklyHours,
        ];
    }

    private function resolveProjectedPg2Students(?int $programId, ?ResearchStaffLoadProjection $projection = null): int
    {
        if (! $programId) {
            return $projection?->projected_pg2_students ? (int) $projection->projected_pg2_students : 0;
        }

        if ($projection && ! $this->periods->isCurrentTarget($projection->academic_period_id)) {
            return (int) $projection->projected_pg2_students;
        }

        return $this->students->projectedPg2StudentsForProgram($programId);
    }
}
