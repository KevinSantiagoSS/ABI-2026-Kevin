<?php

namespace App\Services\Projects;

use App\Models\InvestigationLine;
use App\Models\Professor;
use App\Models\Project;
use App\Models\ThematicArea;
use App\Models\User;
use App\Services\AcademicCalendar\AcademicCalendarService;
use Illuminate\Support\Collection;

class TeacherIdeaBalanceService
{
    public function recommendationsForUser(?User $user): array
    {
        $professor = $this->resolveProfessor($user);

        if (! $professor) {
            return $this->emptyState('No se encontro un perfil docente para calcular recomendaciones.');
        }

        return $this->recommendationsForProfessor($professor);
    }

    public function recommendationsForProfessor(Professor $professor): array
    {
        $professor->loadMissing(['cityProgram.program.researchGroup', 'cityProgram.city', 'user']);

        $activePeriod = AcademicCalendarService::currentActivePeriod();
        $program = $professor->cityProgram?->program;
        $programId = $professor->cityProgram?->program_id;
        $researchGroupId = $program?->research_group_id;

        if (! $activePeriod) {
            return $this->emptyState('No existe un periodo academico activo para calcular recomendaciones.');
        }

        if (! $programId || ! $researchGroupId) {
            return $this->emptyState('No se encontro un programa academico asociado al perfil docente.');
        }

        $lines = InvestigationLine::query()
            ->with(['thematicAreas' => fn ($query) => $query->orderBy('name')])
            ->where('research_group_id', $researchGroupId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $areas = $lines
            ->flatMap(fn (InvestigationLine $line) => $line->thematicAreas)
            ->sortBy('name')
            ->values();

        $ideas = Project::query()
            ->with(['thematicArea.investigationLine', 'projectStatus'])
            ->where('proposal_academic_period_id', $activePeriod->id)
            ->whereHas('projectStatus', fn ($query) => $query->where('name', 'Aprobado'))
            ->whereDoesntHave('students')
            ->whereHas('professors.cityProgram', fn ($query) => $query->where('program_id', $programId))
            ->get()
            ->unique('id')
            ->values();

        $lineStats = $this->buildLineStats($lines, $ideas);
        $areaStats = $this->buildAreaStats($areas, $ideas);
        $alerts = $this->buildAlerts($lineStats, $areaStats, $ideas->count());

        return [
            'active_period' => $activePeriod,
            'program' => $program,
            'professor' => $professor,
            'total_approved_unassigned' => $ideas->count(),
            'line_stats' => $lineStats,
            'area_stats' => $areaStats,
            'alerts' => $alerts,
            'has_data' => true,
            'empty_message' => null,
        ];
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

    protected function buildLineStats(Collection $lines, Collection $ideas): Collection
    {
        return $lines->map(function (InvestigationLine $line) use ($ideas) {
            $count = $ideas->filter(
                fn (Project $project) => (int) ($project->thematicArea?->investigationLine?->id ?? 0) === (int) $line->id
            )->count();

            return [
                'id' => (int) $line->id,
                'name' => $line->name,
                'count' => $count,
            ];
        })->values();
    }

    protected function buildAreaStats(Collection $areas, Collection $ideas): Collection
    {
        return $areas->map(function (ThematicArea $area) use ($ideas) {
            $count = $ideas->filter(
                fn (Project $project) => (int) ($project->thematic_area_id ?? 0) === (int) $area->id
            )->count();

            return [
                'id' => (int) $area->id,
                'name' => $area->name,
                'line_name' => $area->investigationLine?->name,
                'count' => $count,
            ];
        })->values();
    }

    protected function buildAlerts(Collection $lineStats, Collection $areaStats, int $totalIdeas): Collection
    {
        $alerts = collect();

        if ($totalIdeas === 0) {
            $alerts->push([
                'section' => 'recommended',
                'level' => 'success',
                'title' => 'Programa con oportunidad',
                'message' => 'Todavia no hay ideas disponibles en estas categorias para tu programa en el periodo activo.',
            ]);
        }

        $lineAverage = $lineStats->count() > 0 ? $lineStats->avg('count') : 0;
        $areaAverage = $areaStats->count() > 0 ? $areaStats->avg('count') : 0;

        $lineStats
            ->filter(fn (array $line) => $line['count'] === 0)
            ->take(3)
            ->each(fn (array $line) => $alerts->push([
                'section' => 'recommended',
                'level' => 'success',
                'title' => 'Linea sin ideas',
                'message' => 'Faltan ideas de la linea ' . $line['name'] . '.',
            ]));

        $lineStats
            ->filter(fn (array $line) => $line['count'] === 1)
            ->take(2)
            ->each(fn (array $line) => $alerts->push([
                'section' => 'low',
                'level' => 'warning',
                'title' => 'Linea con baja disponibilidad',
                'message' => 'La linea ' . $line['name'] . ' tiene poca disponibilidad de ideas en este periodo.',
            ]));

        $areaStats
            ->filter(fn (array $area) => $area['count'] === 0)
            ->take(3)
            ->each(fn (array $area) => $alerts->push([
                'section' => 'recommended',
                'level' => 'success',
                'title' => 'Area sin ideas',
                'message' => 'Hay ausencia de ideas en el area ' . $area['name'] . '.',
            ]));

        $areaStats
            ->filter(fn (array $area) => $area['count'] === 1)
            ->take(2)
            ->each(fn (array $area) => $alerts->push([
                'section' => 'low',
                'level' => 'warning',
                'title' => 'Area con baja disponibilidad',
                'message' => 'El area ' . $area['name'] . ' tiene muy pocas ideas disponibles en este periodo.',
            ]));

        $lineStats
            ->filter(fn (array $line) => $line['count'] >= max(3, (int) ceil($lineAverage * 1.8)) && $this->share($line['count'], $totalIdeas) >= 0.35)
            ->take(3)
            ->each(fn (array $line) => $alerts->push([
                'section' => 'avoid',
                'level' => 'danger',
                'title' => 'Linea repetida',
                'message' => 'Hay muchas ideas de la linea ' . $line['name'] . ', evitar proponer mas de esta linea.',
            ]));

        $areaStats
            ->filter(fn (array $area) => $area['count'] >= max(2, (int) ceil($areaAverage * 2)) && $this->share($area['count'], $totalIdeas) >= 0.30)
            ->take(3)
            ->each(fn (array $area) => $alerts->push([
                'section' => 'avoid',
                'level' => 'danger',
                'title' => 'Area repetida',
                'message' => 'Hay muchas ideas del area ' . $area['name'] . ', evitar repetir propuestas de esta area.',
            ]));

        if ($alerts->isEmpty()) {
            $alerts->push([
                'section' => 'recommended',
                'level' => 'success',
                'title' => 'Distribucion estable',
                'message' => 'No se detectan faltantes criticos ni repeticiones marcadas en tu programa para el periodo activo.',
            ]);
        }

        return $alerts->values();
    }

    protected function share(int $count, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return $count / $total;
    }

    protected function emptyState(string $message): array
    {
        return [
            'active_period' => AcademicCalendarService::currentActivePeriod(),
            'program' => null,
            'professor' => null,
            'total_approved_unassigned' => 0,
            'line_stats' => collect(),
            'area_stats' => collect(),
            'alerts' => collect(),
            'has_data' => false,
            'empty_message' => $message,
        ];
    }
}
