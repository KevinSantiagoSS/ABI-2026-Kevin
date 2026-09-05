<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoadProjectionRequest;
use App\Models\AcademicProcessWindow;
use App\Models\ResearchStaff\ResearchStaffLoadProjection;
use App\Models\ResearchStaff\ResearchStaffProgram;
use App\Services\AcademicCalendar\AcademicCalendarService;
use App\Services\Projections\LoadProjectionService;
use App\Services\Projections\ProjectionPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoadProjectionController extends Controller
{
    public function __construct(
        private readonly ProjectionPeriodService $periods,
        private readonly LoadProjectionService $loadProjections,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION);
        }

        $targetPeriod = $this->periods->targetPeriod();
        $selectedPeriodId = $request->integer('academic_period_id') ?: $targetPeriod?->id;
        $selectedProgramId = $request->integer('program_id');
        $selectedProgramIds = ResearchStaffProgram::equivalentIds($selectedProgramId);
        $perPage = max(10, min((int) $request->input('per_page', 10), 100));

        $projections = ResearchStaffLoadProjection::query()
            ->with(['academicPeriod', 'program.researchGroup'])
            ->when($selectedPeriodId, fn ($query) => $query->where('academic_period_id', $selectedPeriodId))
            ->when($selectedProgramIds !== [], fn ($query) => $query->whereIn('program_id', $selectedProgramIds))
            ->orderByDesc('academic_period_id')
            ->orderBy('program_id')
            ->paginate($perPage)
            ->appends($request->query());

        return view('projections.load-projections.index', [
            'projections' => $projections,
            'periods' => $this->periods->allPeriods(),
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'targetPeriod' => $targetPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedProgramId' => $selectedProgramId,
            'perPage' => $perPage,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION);
        }

        $targetPeriod = $this->periods->targetPeriod();
        $selectedProgramId = old('program_id') ? (int) old('program_id') : null;

        return view('projections.load-projections.create', [
            'loadProjection' => new ResearchStaffLoadProjection(),
            'targetPeriod' => $targetPeriod,
            'activePeriod' => $this->periods->activePeriod(),
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'previewMetrics' => $this->loadProjections->preview($selectedProgramId, old('projected_pg1_students')),
            'lockProgram' => false,
            'isCurrentTarget' => true,
        ]);
    }

    public function store(LoadProjectionRequest $request): RedirectResponse|View
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION);
        }

        [$projection, $created] = $this->loadProjections->upsert($request->validated());

        return redirect()
            ->route('projections.load-projections.index', [
                'academic_period_id' => $projection->academic_period_id,
                'program_id' => $projection->program_id,
            ])
            ->with(
                'success',
                $created
                    ? 'Proyeccion de carga registrada correctamente.'
                    : 'Ya existia una proyeccion para este programa y periodo. La informacion fue actualizada.'
            );
    }

    public function edit(ResearchStaffLoadProjection $load_projection): View|RedirectResponse
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION);
        }

        $load_projection->load(['academicPeriod', 'program.researchGroup']);

        return view('projections.load-projections.edit', [
            'loadProjection' => $load_projection,
            'targetPeriod' => $load_projection->academicPeriod,
            'activePeriod' => $this->periods->activePeriod(),
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'previewMetrics' => $this->loadProjections->preview(
                (int) $load_projection->program_id,
                old('projected_pg1_students', $load_projection->projected_pg1_students),
                $load_projection
            ),
            'lockProgram' => true,
            'isCurrentTarget' => $this->periods->isCurrentTarget($load_projection->academic_period_id),
        ]);
    }

    public function update(LoadProjectionRequest $request, ResearchStaffLoadProjection $load_projection): RedirectResponse|View
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_LOAD_PROJECTION);
        }

        $this->loadProjections->update($load_projection, $request->validated());

        return redirect()
            ->route('projections.load-projections.index', [
                'academic_period_id' => $load_projection->academic_period_id,
                'program_id' => $load_projection->program_id,
            ])
            ->with('success', 'Proyeccion de carga actualizada correctamente.');
    }
}
