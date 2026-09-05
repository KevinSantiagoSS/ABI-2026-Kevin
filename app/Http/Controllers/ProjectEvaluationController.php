<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\Professor;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Services\AcademicCalendar\AcademicCalendarService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectEvaluationController extends Controller
{
    public function index(): View
    {
        $committeeLeader = $this->resolveCommitteeLeader();
        $cityProgramId = (int) $committeeLeader->city_program_id;

        $projects = $this->programProjectsQuery($cityProgramId)
            ->whereHas('projectStatus', function (Builder $query): void {
                $query->where('name', 'Pendiente de aprobacion');
            })
            ->with([
                'projectStatus',
                'thematicArea.investigationLine',
                'versions.contentVersions.content',
                'contentFrameworkProjects.contentFramework.framework',
                'students',
                'professors',
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('projects.evaluation.index', [
            'projects' => $projects,
            'committeeLeader' => $committeeLeader->loadMissing('cityProgram.program', 'cityProgram.city'),
            'reportState' => $this->buildCommitteeReportState($cityProgramId, $projects->count()),
        ]);
    }

    public function show(Project $project)
    {
        $committeeLeader = $this->resolveCommitteeLeader();
        $this->ensureProjectBelongsToCommitteeProgram($project, (int) $committeeLeader->city_program_id);

        $project->load([
            'thematicArea.investigationLine',
            'projectStatus',
            'professors.user',
            'professors.cityProgram.program',
            'students',
            'contentFrameworks.framework',
            'versions' => static fn ($relation) => $relation->with(['contentVersions.content'])->orderByDesc('created_at'),
        ]);

        $latestVersion = $project->versions()->latest('created_at')->first();
        $contentValues = [];
        if ($latestVersion) {
            foreach ($latestVersion->contentVersions as $cv) {
                $label = $cv->content->label ?? $cv->content->name ?? 'Campo';
                $contentValues[$label] = $cv->value ?? '-';
            }
        }
        $frameworksSelected = $project->contentFrameworks;

        return view('projects.evaluation.show', compact('project', 'latestVersion', 'contentValues', 'frameworksSelected'));
    }

    public function evaluate(Request $request, Project $project)
    {
        $committeeLeader = $this->resolveCommitteeLeader();
        $this->ensureProjectBelongsToCommitteeProgram($project, (int) $committeeLeader->city_program_id);

        $validated = $request->validate([
            'status' => 'required|string|in:Aprobado,Rechazado,Devuelto para correccion,Devuelto para corrección',
            'comments' => 'nullable|string',
        ]);

        $requestedStatus = Str::of($validated['status'])->ascii()->squish()->toString();
        $statusName = $requestedStatus;
        $isProfessorProject = $project->professors()->exists();
        $isStudentProject = ! $isProfessorProject;
        if ($statusName === 'Aprobado' && $isStudentProject) {
            $statusName = 'Asignado';
        }

        $status = ProjectStatus::whereIn('name', array_unique([$statusName, Str::of($statusName)->ascii()->toString()]))->first();
        if (! $status) {
            return back()->with('error', "No se encontro el estado '$statusName'.");
        }

        $project->update(['project_status_id' => $status->id]);

        if ($requestedStatus === 'Devuelto para correccion') {
            $latestVersion = $project->versions()->latest('created_at')->first();
            if ($latestVersion) {
                $commentContent = Content::where('name', 'Comentarios')->whereJsonContains('roles', 'committee_leader')->first();
                if ($commentContent) {
                    ContentVersion::create([
                        'version_id' => $latestVersion->id,
                        'content_id' => $commentContent->id,
                        'value' => $validated['comments'] ?? 'Sin comentarios',
                    ]);
                }
            }
        }

        $activePeriod = \App\Models\AcademicPeriod::query()->active()->first() ?? $project->proposalAcademicPeriod;
        $stage = match (Str::lower($requestedStatus)) {
            'aprobado' => 'approved',
            'rechazado' => 'rejected',
            'devuelto para correccion' => 'returned_for_correction',
            default => 'evaluated',
        };

        AcademicCalendarService::recordProjectStage(
            $project,
            $stage,
            $activePeriod,
            Auth::id(),
            $validated['comments'] ?? null,
            ['final_status_name' => $statusName]
        );

        return redirect()
            ->route('projects.evaluation.index')
            ->with('success', "Evaluacion del proyecto '{$project->title}' enviada correctamente con estado: $statusName.");
    }

    protected function resolveCommitteeLeader(): Professor
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'committee_leader') {
            abort(403, 'Solo el lider de comite puede consultar este modulo.');
        }

        $professor = Professor::query()
            ->where('user_id', $user->id)
            ->where('committee_leader', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $professor || ! $professor->city_program_id) {
            abort(403, 'No se pudo determinar el programa del lider de comite.');
        }

        return $professor;
    }

    protected function programProjectsQuery(int $cityProgramId): Builder
    {
        return Project::query()->where(function (Builder $query) use ($cityProgramId): void {
            $query
                ->whereHas('students', function (Builder $subQuery) use ($cityProgramId): void {
                    $subQuery->where('city_program_id', $cityProgramId);
                })
                ->orWhereHas('professors', function (Builder $subQuery) use ($cityProgramId): void {
                    $subQuery->where('city_program_id', $cityProgramId);
                });
        });
    }

    /**
     * @return array{
     *     totals: array{evaluated:int,pending:int,approved:int,rejected:int,returned:int},
     *     periods: array<int, array{label:string,value:int,percentage:float,color:string}>,
     *     statuses: array<int, array{label:string,value:int,percentage:float,color:string}>,
     *     rates: array<int, array{label:string,value:int,percentage:float,color:string,description:string}>,
     *     topPeriod:?string
     * }
     */
    protected function buildCommitteeReportState(int $cityProgramId, int $pendingProjects): array
    {
        $evaluationProjects = $this->programProjectsQuery($cityProgramId)
            ->whereHas('stageHistories', function (Builder $query): void {
                $query->whereIn('stage', ['approved', 'rejected', 'returned_for_correction']);
            })
            ->with([
                'projectStatus',
                'stageHistories' => function ($query) {
                    $query
                        ->whereIn('stage', ['approved', 'rejected', 'returned_for_correction'])
                        ->with('academicPeriod')
                        ->orderByDesc('event_at')
                        ->orderByDesc('id');
                },
            ])
            ->get();

        $latestEvaluations = $evaluationProjects
            ->map(function (Project $project): ?array {
                $latestHistory = $project->stageHistories->first();

                if (! $latestHistory) {
                    return null;
                }

                $period = $latestHistory->academicPeriod;

                return [
                    'period_label' => $period?->name ?? 'Sin periodo academico',
                    'period_sort' => $period?->start_date?->timestamp ?? PHP_INT_MAX,
                    'status_label' => $this->committeeStatusLabel(
                        $latestHistory->metadata['final_status_name'] ?? null,
                        $latestHistory->stage,
                        $project->projectStatus?->name
                    ),
                ];
            })
            ->filter()
            ->values();

        $totalEvaluated = $latestEvaluations->count();
        $periodPalette = [
            '#2563eb',
            '#0f766e',
            '#b45309',
            '#be123c',
            '#7c3aed',
            '#0891b2',
            '#4d7c0f',
            '#ea580c',
        ];

        $periods = $latestEvaluations
            ->groupBy('period_label')
            ->map(function ($items, $label) use ($totalEvaluated) {
                return [
                    'label' => (string) $label,
                    'value' => $items->count(),
                    'percentage' => $totalEvaluated > 0
                        ? round(($items->count() / $totalEvaluated) * 100, 2)
                        : 0.0,
                    'sort' => $items->min('period_sort'),
                ];
            })
            ->sortBy('sort')
            ->values()
            ->map(function (array $period, int $index) use ($periodPalette): array {
                unset($period['sort']);
                $period['color'] = $periodPalette[$index % count($periodPalette)];

                return $period;
            })
            ->all();

        $statusDefinitions = [
            'Aprobado' => '#16a34a',
            'Rechazado' => '#dc2626',
            'Devuelto para correccion' => '#d97706',
        ];

        $statuses = collect($statusDefinitions)
            ->map(function (string $color, string $label) use ($latestEvaluations, $totalEvaluated): array {
                $value = $latestEvaluations->where('status_label', $label)->count();

                return [
                    'label' => $label,
                    'value' => $value,
                    'percentage' => $totalEvaluated > 0 ? round(($value / $totalEvaluated) * 100, 2) : 0.0,
                    'color' => $color,
                ];
            })
            ->values()
            ->all();

        $rates = [
            [
                'label' => 'Aceptacion',
                'value' => $statuses[0]['value'] ?? 0,
                'percentage' => $statuses[0]['percentage'] ?? 0.0,
                'color' => $statuses[0]['color'] ?? '#16a34a',
                'description' => 'Ideas que terminaron con concepto favorable del comite.',
            ],
            [
                'label' => 'Rechazo',
                'value' => $statuses[1]['value'] ?? 0,
                'percentage' => $statuses[1]['percentage'] ?? 0.0,
                'color' => $statuses[1]['color'] ?? '#dc2626',
                'description' => 'Ideas descartadas de forma definitiva por el comite.',
            ],
            [
                'label' => 'Devolucion',
                'value' => $statuses[2]['value'] ?? 0,
                'percentage' => $statuses[2]['percentage'] ?? 0.0,
                'color' => $statuses[2]['color'] ?? '#d97706',
                'description' => 'Ideas que fueron devueltas para ajustes o correcciones.',
            ],
        ];

        return [
            'totals' => [
                'evaluated' => $totalEvaluated,
                'pending' => $pendingProjects,
                'approved' => $statuses[0]['value'] ?? 0,
                'rejected' => $statuses[1]['value'] ?? 0,
                'returned' => $statuses[2]['value'] ?? 0,
            ],
            'periods' => $periods,
            'statuses' => $statuses,
            'rates' => $rates,
            'topPeriod' => collect($periods)->sortByDesc('value')->first()['label'] ?? null,
        ];
    }

    protected function committeeStatusLabel(?string $finalStatusName, ?string $stage, ?string $fallbackStatusName): string
    {
        $normalized = Str::of((string) ($finalStatusName ?: $fallbackStatusName))
            ->ascii()
            ->lower()
            ->squish()
            ->toString();

        return match (true) {
            $normalized === 'aprobado',
            $stage === 'approved' => 'Aprobado',
            $normalized === 'rechazado',
            $stage === 'rejected' => 'Rechazado',
            $normalized === 'devuelto para correccion',
            $stage === 'returned_for_correction' => 'Devuelto para correccion',
            default => 'Sin estado final',
        };
    }

    protected function ensureProjectBelongsToCommitteeProgram(Project $project, int $cityProgramId): void
    {
        $belongsToProgram = $this->programProjectsQuery($cityProgramId)
            ->whereKey($project->getKey())
            ->exists();

        if (! $belongsToProgram) {
            abort(403, 'Este proyecto no pertenece al programa del lider de comite autenticado.');
        }
    }
}