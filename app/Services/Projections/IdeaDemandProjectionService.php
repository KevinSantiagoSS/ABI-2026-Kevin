<?php

namespace App\Services\Projections;

use App\Models\ResearchStaff\ResearchStaffInvestigationLine;
use App\Models\ResearchStaff\ResearchStaffLoadProjection;
use App\Models\ResearchStaff\ResearchStaffProject;
use App\Models\ResearchStaff\ResearchStaffProgram;
use Illuminate\Support\Collection;

class IdeaDemandProjectionService
{
    public function summaryForPeriod(?int $academicPeriodId, ?int $programId = null): array
    {
        $selectedProgramIds = ResearchStaffProgram::equivalentIds($programId);

        $projections = ResearchStaffLoadProjection::query()
            ->with(['academicPeriod', 'program.researchGroup'])
            ->when($academicPeriodId, fn ($query) => $query->where('academic_period_id', $academicPeriodId))
            ->when($selectedProgramIds !== [], fn ($query) => $query->whereIn('program_id', $selectedProgramIds))
            ->orderBy('academic_period_id')
            ->orderBy('program_id')
            ->get();

        $programIds = $projections->pluck('program_id')->filter()->unique()->values();
        $researchGroupIds = $projections->pluck('program.research_group_id')->filter()->unique()->values();

        $availableIdeas = $this->availableIdeasByProgram($programIds);
        $lineCatalog = ResearchStaffInvestigationLine::query()
            ->with('thematicAreas')
            ->whereIn('research_group_id', $researchGroupIds->all())
            ->orderBy('name')
            ->get()
            ->groupBy('research_group_id');

        $rows = $projections->map(function (ResearchStaffLoadProjection $projection) use ($availableIdeas, $lineCatalog) {
            $programIdeaData = $availableIdeas[$projection->program_id] ?? [
                'project_ids' => [],
                'lines' => [],
                'areas' => [],
            ];

            $researchGroupId = $projection->program?->research_group_id;
            $groupLines = $lineCatalog[$researchGroupId] ?? collect();
            $lineBreakdown = collect($programIdeaData['lines'] ?? [])->sortByDesc('count')->values();
            $areaBreakdown = collect($programIdeaData['areas'] ?? [])->sortByDesc('count')->values();
            $availableCount = count($programIdeaData['project_ids'] ?? []);
            $neededIdeas = (int) $projection->projected_pg1_groups;

            return [
                'projection' => $projection,
                'program' => $projection->program,
                'academic_period' => $projection->academicPeriod,
                'needed_ideas' => $neededIdeas,
                'available_ideas' => $availableCount,
                'missing_ideas' => max($neededIdeas - $availableCount, 0),
                'excess_ideas' => max($availableCount - $neededIdeas, 0),
                'line_breakdown' => $lineBreakdown,
                'area_breakdown' => $areaBreakdown,
                'alerts' => $this->buildAlerts($neededIdeas, $availableCount, $groupLines, $lineBreakdown, $areaBreakdown),
            ];
        })->values();

        return [
            'rows' => $rows,
            'totals' => [
                'projected_programs' => $rows->count(),
                'needed_ideas' => $rows->sum('needed_ideas'),
                'available_ideas' => $rows->sum('available_ideas'),
                'missing_ideas' => $rows->sum('missing_ideas'),
            ],
        ];
    }

    private function availableIdeasByProgram(Collection $programIds): array
    {
        if ($programIds->isEmpty()) {
            return [];
        }

        $projects = ResearchStaffProject::query()
            ->with([
                'projectStatus',
                'thematicArea.investigationLine',
                'professors.cityProgram.program',
                'students',
            ])
            ->get()
            ->filter(function ($project) use ($programIds) {
                $projectProgramIds = $project->professors
                    ->pluck('cityProgram.program_id')
                    ->filter()
                    ->unique();

                return $project->projectStatus?->name === 'Aprobado'
                    && $project->students->isEmpty()
                    && $projectProgramIds->intersect($programIds)->isNotEmpty();
            })
            ->values();

        $availableIdeas = [];

        foreach ($projects as $project) {
            $programsForProject = $project->professors
                ->pluck('cityProgram.program_id')
                ->filter()
                ->unique()
                ->values();

            $line = $project->thematicArea?->investigationLine;
            $area = $project->thematicArea;

            foreach ($programsForProject as $programId) {
                $availableIdeas[$programId] ??= [
                    'project_ids' => [],
                    'lines' => [],
                    'areas' => [],
                ];

                $availableIdeas[$programId]['project_ids'][$project->id] = true;

                if ($line) {
                    $availableIdeas[$programId]['lines'][$line->id] ??= [
                        'id' => $line->id,
                        'name' => $line->name,
                        'count' => 0,
                    ];

                    $availableIdeas[$programId]['lines'][$line->id]['count']++;
                }

                if ($area) {
                    $availableIdeas[$programId]['areas'][$area->id] ??= [
                        'id' => $area->id,
                        'name' => $area->name,
                        'line_name' => $line?->name,
                        'count' => 0,
                    ];

                    $availableIdeas[$programId]['areas'][$area->id]['count']++;
                }
            }
        }

        return $availableIdeas;
    }

    private function buildAlerts(
        int $neededIdeas,
        int $availableIdeas,
        Collection $groupLines,
        Collection $lineBreakdown,
        Collection $areaBreakdown
    ): array {
        $alerts = [];

        if ($neededIdeas > 0 && $availableIdeas === 0) {
            $alerts[] = ['level' => 'danger', 'message' => 'No hay ideas aprobadas y sin asignar para cubrir la demanda PG1 proyectada.'];
        } elseif ($neededIdeas > $availableIdeas) {
            $alerts[] = ['level' => 'warning', 'message' => 'La disponibilidad actual no cubre la demanda PG1 del periodo objetivo.'];
        } else {
            $alerts[] = ['level' => 'success', 'message' => 'La cantidad total de ideas aprobadas disponibles cubre la demanda PG1 proyectada.'];
        }

        $lineIdsWithIdeas = $lineBreakdown->pluck('id')->all();
        $linesWithoutIdeas = $groupLines->whereNotIn('id', $lineIdsWithIdeas)->pluck('name')->take(3)->values();

        if ($linesWithoutIdeas->isNotEmpty()) {
            $alerts[] = [
                'level' => 'warning',
                'message' => 'No hay ideas disponibles en las lineas: ' . $linesWithoutIdeas->implode(', ') . '.',
            ];
        }

        $areasWithoutIdeas = $groupLines
            ->flatMap(fn ($line) => $line->thematicAreas->whereNotIn('id', $areaBreakdown->pluck('id')->all())->pluck('name'))
            ->take(3)
            ->values();

        if ($areasWithoutIdeas->isNotEmpty()) {
            $alerts[] = [
                'level' => 'warning',
                'message' => 'Hay ausencia de ideas en areas tematicas como: ' . $areasWithoutIdeas->implode(', ') . '.',
            ];
        }

        $topLine = $lineBreakdown->first();
        if ($topLine && $availableIdeas >= 3 && ($topLine['count'] / max($availableIdeas, 1)) >= 0.6) {
            $alerts[] = [
                'level' => 'info',
                'message' => 'La linea "' . $topLine['name'] . '" concentra la mayor parte del banco disponible.',
            ];
        }

        $topArea = $areaBreakdown->first();
        if ($topArea && $availableIdeas >= 4 && ($topArea['count'] / max($availableIdeas, 1)) >= 0.5) {
            $alerts[] = [
                'level' => 'info',
                'message' => 'El area "' . $topArea['name'] . '" presenta alta concentracion frente al resto del banco.',
            ];
        }

        if ($groupLines->isEmpty() && $availableIdeas > 0) {
            $alerts[] = [
                'level' => 'secondary',
                'message' => 'El programa no tiene lineas y areas catalogadas para analizar balance tematico completo.',
            ];
        }

        return array_slice($alerts, 0, 5);
    }
}
