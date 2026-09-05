<?php

namespace App\Http\Controllers;

use App\Models\AcademicProcessWindow;
use App\Models\ResearchStaff\ResearchStaffProgram;
use App\Services\AcademicCalendar\AcademicCalendarService;
use App\Services\Projections\IdeaDemandProjectionService;
use App\Services\Projections\ProjectionPeriodService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IdeaDemandController extends Controller
{
    public function __construct(
        private readonly ProjectionPeriodService $periods,
        private readonly IdeaDemandProjectionService $demand,
    ) {
    }

    public function index(Request $request): View
    {
        if (! AcademicCalendarService::isProcessWindowOpen(AcademicProcessWindow::PROCESS_IDEA_DEMAND_PROJECTION)) {
            return $this->academicProcessUnavailableView(AcademicProcessWindow::PROCESS_IDEA_DEMAND_PROJECTION);
        }

        $targetPeriod = $this->periods->targetPeriod();
        $selectedPeriodId = $request->integer('academic_period_id') ?: $targetPeriod?->id;
        $selectedProgramId = $request->integer('program_id');
        $summary = $this->demand->summaryForPeriod($selectedPeriodId, $selectedProgramId);
        $selectedProgramIds = ResearchStaffProgram::equivalentIds($selectedProgramId);
        $detailRow = $selectedProgramId
            ? $summary['rows']->first(fn ($row) => in_array((int) $row['program']?->id, $selectedProgramIds, true))
            : ($summary['rows']->count() === 1 ? $summary['rows']->first() : null);

        return view('projections.idea-demand.index', [
            'periods' => $this->periods->allPeriods(),
            'programs' => ResearchStaffProgram::uniqueOptions(),
            'targetPeriod' => $targetPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedProgramId' => $selectedProgramId,
            'summary' => $summary,
            'detailRow' => $detailRow,
        ]);
    }
}
