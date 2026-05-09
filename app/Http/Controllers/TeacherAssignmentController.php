<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherAssignmentRequest;
use App\Models\AcademicProcessWindow;
use App\Models\ResearchStaff\ResearchStaffProgram;
use App\Models\ResearchStaff\ResearchStaffTeacherAssignment;
use App\Services\AcademicCalendar\AcademicCalendarService;
use App\Services\Projections\ProjectionPeriodService;
use App\Services\Projections\TeacherProjectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherAssignmentController extends Controller
{
    public function __construct(
        private readonly ProjectionPeriodService $periods,
        private readonly TeacherProjectionService $teachers,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT);
        }

        $targetPeriod = $this->periods->targetPeriod();
        $selectedPeriodId = $request->integer('academic_period_id') ?: $targetPeriod?->id;
        $selectedProgramId = $request->integer('program_id');
        $selectedProgramIds = ResearchStaffProgram::equivalentIds($selectedProgramId);
        $perPage = max(10, min((int) $request->input('per_page', 10), 100));

        $assignments = ResearchStaffTeacherAssignment::query()
            ->with(['academicPeriod', 'program', 'professor.user', 'professor.cityProgram.city'])
            ->when($selectedPeriodId, fn ($query) => $query->where('academic_period_id', $selectedPeriodId))
            ->when($selectedProgramIds !== [], fn ($query) => $query->whereIn('program_id', $selectedProgramIds))
            ->orderByDesc('academic_period_id')
            ->orderBy('program_id')
            ->paginate($perPage)
            ->appends($request->query());

        $assignments->setCollection($this->teachers->decorateAssignments($assignments->getCollection(), $selectedPeriodId));

        return view('projections.teacher-assignments.index', [
            'assignments' => $assignments,
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
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT);
        }

        return view('projections.teacher-assignments.create', [
            'teacherAssignment' => new ResearchStaffTeacherAssignment(),
            'targetPeriod' => $this->periods->targetPeriod(),
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'teacherDirectory' => $this->teachers->teacherDirectoryByProgram(),
            'lockIdentityFields' => false,
        ]);
    }

    public function store(TeacherAssignmentRequest $request): RedirectResponse|View
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT);
        }

        [$assignment, $created] = $this->teachers->upsert($request->validated());

        return redirect()
            ->route('projections.teacher-assignments.index', [
                'academic_period_id' => $assignment->academic_period_id,
                'program_id' => $assignment->program_id,
            ])
            ->with(
                'success',
                $created
                    ? 'Asignacion docente registrada correctamente.'
                    : 'Ya existia una asignacion para este docente y periodo. La informacion fue actualizada.'
            );
    }

    public function edit(ResearchStaffTeacherAssignment $teacher_assignment): View|RedirectResponse
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT);
        }

        $teacher_assignment->load(['academicPeriod', 'program', 'professor.user', 'professor.cityProgram.city']);

        return view('projections.teacher-assignments.edit', [
            'teacherAssignment' => $teacher_assignment,
            'targetPeriod' => $teacher_assignment->academicPeriod,
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'teacherDirectory' => [],
            'lockIdentityFields' => true,
        ]);
    }

    public function update(
        TeacherAssignmentRequest $request,
        ResearchStaffTeacherAssignment $teacher_assignment
    ): RedirectResponse|View {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_TEACHER_ASSIGNMENT);
        }

        $this->teachers->update($teacher_assignment, $request->validated());

        return redirect()
            ->route('projections.teacher-assignments.index', [
                'academic_period_id' => $teacher_assignment->academic_period_id,
                'program_id' => $teacher_assignment->program_id,
            ])
            ->with('success', 'Asignacion docente actualizada correctamente.');
    }
}
