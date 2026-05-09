<?php

namespace App\Services\Projections;

use App\Models\ResearchStaff\ResearchStaffProfessor;
use App\Models\ResearchStaff\ResearchStaffProject;
use App\Models\ResearchStaff\ResearchStaffTeacherAssignment;
use Illuminate\Support\Collection;

class TeacherProjectionService
{
    public function __construct(
        private readonly ProjectionPeriodService $periods,
        private readonly StudentProjectionService $students,
    ) {
    }

    public function teacherDirectoryByProgram(): array
    {
        $continuitySuggestions = $this->students->continuitySuggestionsByProgram();

        $professors = ResearchStaffProfessor::query()
            ->with(['user', 'cityProgram.city', 'cityProgram.program'])
            ->orderBy('last_name')
            ->orderBy('name')
            ->get()
            ->filter(fn ($professor) => ($professor->user?->state ?? false) && ! $professor->trashed())
            ->values();

        $directory = [];

        foreach ($professors as $professor) {
            $program = $professor->cityProgram?->program;
            $city = $professor->cityProgram?->city;

            if (! $program) {
                continue;
            }

            $programId = (int) $program->id;
            $suggestion = $continuitySuggestions[$programId][(int) $professor->id] ?? null;
            $fullName = trim(($professor->name ?? '') . ' ' . ($professor->last_name ?? ''));

            $directory[$programId] ??= [
                'program_id' => $programId,
                'program_name' => $program->name,
                'teachers' => [],
                'suggestions' => [],
            ];

            $directory[$programId]['teachers'][] = [
                'id' => (int) $professor->id,
                'label' => $fullName,
                'city' => $city?->name,
                'suggested' => $suggestion !== null,
                'continuity_groups' => $suggestion['group_count'] ?? 0,
                'continuity_students' => $suggestion['student_count'] ?? 0,
            ];
        }

        foreach ($continuitySuggestions as $programId => $suggestions) {
            $directory[$programId]['suggestions'] = array_values($suggestions);
        }

        foreach ($directory as $programId => $programData) {
            usort($programData['teachers'], function (array $left, array $right): int {
                return [
                    $right['suggested'],
                    $right['continuity_groups'],
                    $right['continuity_students'],
                    $left['city'],
                    $left['label'],
                ] <=> [
                    $left['suggested'],
                    $left['continuity_groups'],
                    $left['continuity_students'],
                    $right['city'],
                    $right['label'],
                ];
            });

            usort($programData['suggestions'], function (array $left, array $right): int {
                return [
                    $right['group_count'],
                    $right['student_count'],
                    $left['name'],
                ] <=> [
                    $left['group_count'],
                    $left['student_count'],
                    $right['name'],
                ];
            });

            $directory[$programId] = $programData;
        }

        return $directory;
    }

    public function upsert(array $validated): array
    {
        $targetPeriod = $this->periods->targetPeriodOrFail();

        $assignment = ResearchStaffTeacherAssignment::query()
            ->firstOrNew([
                'academic_period_id' => $targetPeriod->id,
                'program_id' => (int) $validated['program_id'],
                'professor_id' => (int) $validated['professor_id'],
            ]);

        $created = ! $assignment->exists;

        if ($created) {
            $assignment->created_by_user_id = auth()->id();
        }

        $assignment->fill([
            'assigned_hours' => (int) $validated['assigned_hours'],
            'observations' => $validated['observations'] ?? null,
            'updated_by_user_id' => auth()->id(),
        ]);

        $assignment->save();

        return [$assignment, $created];
    }

    public function update(ResearchStaffTeacherAssignment $assignment, array $validated): ResearchStaffTeacherAssignment
    {
        $assignment->fill([
            'assigned_hours' => (int) $validated['assigned_hours'],
            'observations' => $validated['observations'] ?? null,
            'updated_by_user_id' => auth()->id(),
        ]);

        $assignment->save();

        return $assignment;
    }

    public function decorateAssignments(Collection $assignments, ?int $periodId = null): Collection
    {
        $professorIds = $assignments->pluck('professor_id')->filter()->unique()->values();

        if ($professorIds->isEmpty()) {
            return $assignments;
        }

        $registeredIdeas = $this->registeredIdeasByProfessor($professorIds, $periodId);
        $historicalIdeas = $this->registeredIdeasByProfessor($professorIds);

        return $assignments->map(function (ResearchStaffTeacherAssignment $assignment) use ($registeredIdeas, $historicalIdeas) {
            $registeredCount = (int) ($registeredIdeas[$assignment->professor_id] ?? 0);
            $expectedIdeas = (int) $assignment->assigned_hours;

            $assignment->expected_ideas = $expectedIdeas;
            $assignment->registered_ideas = $registeredCount;
            $assignment->missing_ideas = max($expectedIdeas - $registeredCount, 0);
            $assignment->idea_balance = $registeredCount - $expectedIdeas;
            $assignment->historical_registered_ideas = (int) ($historicalIdeas[$assignment->professor_id] ?? 0);
            $assignment->professor_active = (bool) ($assignment->professor?->user?->state ?? false)
                && ! $assignment->professor?->trashed();

            return $assignment;
        });
    }

    private function registeredIdeasByProfessor(Collection $professorIds, ?int $periodId = null): Collection
    {
        return ResearchStaffProject::query()
            ->selectRaw('professor_project.professor_id as professor_id, COUNT(DISTINCT projects.id) as total')
            ->join('professor_project', 'professor_project.project_id', '=', 'projects.id')
            ->whereIn('professor_project.professor_id', $professorIds->all())
            ->when($periodId, fn ($query) => $query->where('proposal_academic_period_id', $periodId))
            ->groupBy('professor_project.professor_id')
            ->pluck('total', 'professor_id');
    }
}
