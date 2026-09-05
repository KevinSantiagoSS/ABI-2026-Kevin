<?php

namespace App\Http\Controllers;

use App\Models\ResearchStaff\ResearchStaffProgram;
use App\Models\ResearchStaff\ResearchStaffTeacherAssignment;
use App\Services\Projections\ProjectionPeriodService;
use App\Services\Projections\TeacherProjectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectionProfessorController extends Controller
{
    public function __construct(
        private readonly ProjectionPeriodService $periods,
        private readonly TeacherProjectionService $teachers,
    ) {
    }

    public function index(Request $request): View
    {
        $targetPeriod = $this->periods->targetPeriod();
        $selectedPeriodId = $request->integer('academic_period_id') ?: $targetPeriod?->id;
        $selectedProgramId = $request->integer('program_id');
        $selectedProgramIds = ResearchStaffProgram::equivalentIds($selectedProgramId);
        $perPage = max(10, min((int) $request->input('per_page', 10), 100));

        $baseQuery = ResearchStaffTeacherAssignment::query()
            ->with(['academicPeriod', 'program', 'professor.user', 'professor.cityProgram.city'])
            ->when($selectedPeriodId, fn ($query) => $query->where('academic_period_id', $selectedPeriodId))
            ->when($selectedProgramIds !== [], fn ($query) => $query->whereIn('program_id', $selectedProgramIds))
            ->orderBy('program_id')
            ->orderBy('professor_id');

        $summaryAssignments = $this->teachers->decorateAssignments((clone $baseQuery)->get(), $selectedPeriodId);
        $assignments = $baseQuery->paginate($perPage)->appends($request->query());
        $assignments->setCollection($this->teachers->decorateAssignments($assignments->getCollection(), $selectedPeriodId));

        return view('projections.professors.index', [
            'assignments' => $assignments,
            'periods' => $this->periods->allPeriods(),
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'targetPeriod' => $targetPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedProgramId' => $selectedProgramId,
            'perPage' => $perPage,
            'summary' => [
                'teachers' => $summaryAssignments->count(),
                'assigned_hours' => $summaryAssignments->sum('assigned_hours'),
                'registered_ideas' => $summaryAssignments->sum('registered_ideas'),
                'missing_ideas' => $summaryAssignments->sum('missing_ideas'),
            ],
        ]);
    }
}
