<?php

namespace App\Services\Projects;

use App\Models\Professor;
use App\Models\Project;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\AcademicCalendar\AcademicCalendarService;
use Illuminate\Support\Collection;

class TeacherWorkloadService
{
    public function summaryForUser(?User $user): array
    {
        $professor = $this->resolveProfessor($user);
        $activePeriod = AcademicCalendarService::currentActivePeriod();

        if (! $professor) {
            return $this->emptySummary($activePeriod, 'No se encontro un perfil docente asociado al usuario autenticado.');
        }

        $professor->loadMissing(['cityProgram.program.researchGroup', 'cityProgram.city', 'user']);

        $assignmentHistory = TeacherAssignment::query()
            ->with(['academicPeriod', 'program', 'createdBy', 'updatedBy'])
            ->where('professor_id', $professor->id)
            ->get()
            ->sortByDesc(fn (TeacherAssignment $assignment) => $assignment->academicPeriod?->start_date?->getTimestamp() ?? 0)
            ->values();

        $periodIds = $assignmentHistory
            ->pluck('academic_period_id')
            ->filter()
            ->unique()
            ->values();

        if ($activePeriod && ! $periodIds->contains($activePeriod->id)) {
            $periodIds->push($activePeriod->id);
        }

        $registeredIdeasByPeriod = $this->registeredIdeasByProfessorAndPeriods($professor->id, $periodIds);
        $activeAssignment = $activePeriod
            ? $assignmentHistory->firstWhere('academic_period_id', $activePeriod->id)
            : null;

        $history = $assignmentHistory
            ->map(fn (TeacherAssignment $assignment) => $this->decorateAssignment(
                $assignment,
                (int) ($registeredIdeasByPeriod[$assignment->academic_period_id] ?? 0)
            ))
            ->values();

        $activeRegisteredIdeas = $activePeriod ? (int) ($registeredIdeasByPeriod[$activePeriod->id] ?? 0) : 0;
        $activeSummary = $activeAssignment
            ? $this->decorateAssignment($activeAssignment, $activeRegisteredIdeas)
            : $this->emptyAssignmentState($activePeriod, $activeRegisteredIdeas);

        $nextAssignment = $history
            ->first(fn (TeacherAssignment $assignment) => (int) $assignment->academic_period_id !== (int) ($activePeriod?->id ?? 0));

        return [
            'professor' => $professor,
            'program' => $professor->cityProgram?->program,
            'city' => $professor->cityProgram?->city,
            'active_period' => $activePeriod,
            'active_assignment' => $activeSummary,
            'history' => $history->take(5)->values(),
            'next_assignment' => $nextAssignment,
            'has_profile' => true,
            'has_active_assignment' => (bool) $activeSummary['has_assignment'],
            'empty_message' => $activeSummary['empty_message'],
        ];
    }

    public function registeredIdeasForProfessorInPeriod(int $professorId, ?int $academicPeriodId): int
    {
        if (! $academicPeriodId) {
            return 0;
        }

        return (int) $this->registeredIdeasByProfessorAndPeriods($professorId, collect([$academicPeriodId]))
            ->get($academicPeriodId, 0);
    }

    protected function resolveProfessor(?User $user): ?Professor
    {
        if (! $user) {
            return null;
        }

        if (($user->relationLoaded('professor') || array_key_exists('professor', $user->getRelations())) && $user->professor) {
            return $user->professor;
        }

        return Professor::query()
            ->with(['cityProgram.program.researchGroup', 'cityProgram.city', 'user'])
            ->where('user_id', $user->id)
            ->first();
    }

    protected function registeredIdeasByProfessorAndPeriods(int $professorId, Collection $periodIds): Collection
    {
        $periodIds = $periodIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($periodIds->isEmpty()) {
            return collect();
        }

        return Project::query()
            ->selectRaw('projects.proposal_academic_period_id as academic_period_id, COUNT(DISTINCT projects.id) as total')
            ->join('professor_project', 'professor_project.project_id', '=', 'projects.id')
            ->where('professor_project.professor_id', $professorId)
            ->whereIn('projects.proposal_academic_period_id', $periodIds->all())
            ->groupBy('projects.proposal_academic_period_id')
            ->pluck('total', 'academic_period_id')
            ->map(fn ($value) => (int) $value);
    }

    protected function decorateAssignment(TeacherAssignment $assignment, int $registeredIdeas): TeacherAssignment
    {
        $expectedIdeas = (int) $assignment->assigned_hours;
        $missingIdeas = max($expectedIdeas - $registeredIdeas, 0);
        $excessIdeas = max($registeredIdeas - $expectedIdeas, 0);
        $sourceUser = $assignment->updatedBy ?? $assignment->createdBy;

        $assignment->expected_ideas = $expectedIdeas;
        $assignment->registered_ideas = $registeredIdeas;
        $assignment->missing_ideas = $missingIdeas;
        $assignment->excess_ideas = $excessIdeas;
        $assignment->goal_reached = $expectedIdeas > 0 && $registeredIdeas >= $expectedIdeas;
        $assignment->source_context = $sourceUser
            ? 'Asignacion registrada por investigaciones. Ultima actualizacion: ' . trim((string) ($sourceUser->name ?? $sourceUser->email))
            : 'Asignacion registrada por investigaciones.';

        return $assignment;
    }

    protected function emptySummary($activePeriod, string $message): array
    {
        return [
            'professor' => null,
            'program' => null,
            'city' => null,
            'active_period' => $activePeriod,
            'active_assignment' => $this->emptyAssignmentState($activePeriod, 0, $message),
            'history' => collect(),
            'next_assignment' => null,
            'has_profile' => false,
            'has_active_assignment' => false,
            'empty_message' => $message,
        ];
    }

    protected function emptyAssignmentState($activePeriod, int $registeredIdeas, ?string $message = null): array
    {
        return [
            'academic_period_id' => $activePeriod?->id,
            'academicPeriod' => $activePeriod,
            'program' => null,
            'assigned_hours' => 0,
            'expected_ideas' => 0,
            'registered_ideas' => $registeredIdeas,
            'missing_ideas' => 0,
            'excess_ideas' => max($registeredIdeas, 0),
            'goal_reached' => false,
            'has_assignment' => false,
            'observations' => null,
            'source_context' => 'La carga la registra el personal de investigaciones.',
            'empty_message' => $message ?: 'No tienes una asignacion de carga registrada para el periodo academico activo.',
        ];
    }
}
