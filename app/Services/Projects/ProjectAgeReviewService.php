<?php

namespace App\Services\Projects;

use App\Models\AcademicPeriod;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

class ProjectAgeReviewService
{
    public const DEFAULT_THRESHOLD_PERIODS = 2;

    public function thresholdPeriods(?int $thresholdPeriods = null): int
    {
        return max(1, $thresholdPeriods ?? self::DEFAULT_THRESHOLD_PERIODS);
    }

    public function referenceAcademicPeriod(): ?AcademicPeriod
    {
        return AcademicPeriod::query()
            ->active()
            ->orderByDesc('start_date')
            ->first()
            ?? AcademicPeriod::query()
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->first();
    }

    /**
     * @return array<int>
     */
    public function eligibleProposalPeriodIds(?int $thresholdPeriods = null, ?AcademicPeriod $referencePeriod = null): array
    {
        $referencePeriod ??= $this->referenceAcademicPeriod();

        if (! $referencePeriod) {
            return [];
        }

        $thresholdPeriods = $this->thresholdPeriods($thresholdPeriods);
        $orderedPeriodIds = AcademicPeriod::query()
            ->orderBy('start_date')
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->all();

        $referenceIndex = array_search($referencePeriod->id, $orderedPeriodIds, true);

        if ($referenceIndex === false || $referenceIndex < $thresholdPeriods) {
            return [];
        }

        return array_slice($orderedPeriodIds, 0, $referenceIndex - $thresholdPeriods + 1);
    }

    public function applyPendingReviewDueToAge(Builder $query, ?int $thresholdPeriods = null): Builder
    {
        $eligibleProposalPeriodIds = $this->eligibleProposalPeriodIds($thresholdPeriods);

        if ($eligibleProposalPeriodIds === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereNotNull('proposal_academic_period_id')
            ->whereIn('proposal_academic_period_id', $eligibleProposalPeriodIds)
            ->whereNull('assignment_academic_period_id')
            ->whereNull('assigned_at')
            ->where(function (Builder $statusQuery) {
                $statusQuery
                    ->whereNull('project_status_id')
                    ->orWhereHas('projectStatus', function (Builder $projectStatusQuery) {
                        $projectStatusQuery->whereNotIn('name', ['Asignado', 'Rechazado']);
                    });
            });
    }

    public function elapsedAcademicPeriods(Project $project, ?AcademicPeriod $referencePeriod = null): ?int
    {
        if (! $project->proposal_academic_period_id) {
            return null;
        }

        $referencePeriod ??= $this->referenceAcademicPeriod();

        if (! $referencePeriod) {
            return null;
        }

        $orderedPeriodIds = AcademicPeriod::query()
            ->orderBy('start_date')
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->all();

        $proposalIndex = array_search((int) $project->proposal_academic_period_id, $orderedPeriodIds, true);
        $referenceIndex = array_search((int) $referencePeriod->id, $orderedPeriodIds, true);

        if ($proposalIndex === false || $referenceIndex === false || $proposalIndex > $referenceIndex) {
            return null;
        }

        return $referenceIndex - $proposalIndex;
    }

    public function shouldFlag(Project $project, ?int $thresholdPeriods = null): bool
    {
        if (! $project->proposal_academic_period_id) {
            return false;
        }

        if ($project->assignment_academic_period_id || $project->assigned_at) {
            return false;
        }

        $statusName = (string) optional($project->projectStatus)->name;

        if (in_array($statusName, ['Asignado', 'Rechazado'], true)) {
            return false;
        }

        $elapsedPeriods = $this->elapsedAcademicPeriods($project);

        if ($elapsedPeriods === null) {
            return false;
        }

        return $elapsedPeriods >= $this->thresholdPeriods($thresholdPeriods);
    }
}
