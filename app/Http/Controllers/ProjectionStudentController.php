<?php

namespace App\Http\Controllers;

use App\Models\ResearchStaff\ResearchStaffProgram;
use App\Services\Projections\StudentProjectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ProjectionStudentController extends Controller
{
    public function __construct(private readonly StudentProjectionService $students)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $selectedProgramId = $request->integer('program_id');
        $selectedStage = (string) $request->input('stage', '');
        $selectedState = $request->input('state');
        $selectedState = in_array((string) $selectedState, ['0', '1'], true)
            ? (string) $selectedState
            : '';
        $perPage = max(10, min((int) $request->input('per_page', 10), 100));
        $page = max((int) $request->input('page', 1), 1);

        $rows = $this->students->supportRows($selectedProgramId ?: null)
            ->when($selectedStage !== '', fn ($collection) => $collection->where('pg_stage_key', $selectedStage))
            ->when($selectedState !== '', function ($collection) use ($selectedState) {
                return $collection->filter(fn (array $row) => (int) $row['is_active'] === (int) $selectedState)->values();
            })
            ->values();

        $summary = [
            'total_students' => $rows->count(),
            'active_students' => $rows->where('is_active', true)->count(),
            'pg1_students' => $rows->where('pg_stage_key', 'pg1')->count(),
            'pg2_students' => $rows->where('pg_stage_key', 'pg2')->count(),
            'projected_pg2_students' => $rows->filter(fn (array $row) => $row['is_active'] && $row['projected_pg2_next_period'])->count(),
        ];

        $lastPage = max((int) ceil(max($rows->count(), 1) / $perPage), 1);

        if ($request->has('page') && $page > $lastPage) {
            return redirect()->route('projections.students.index', array_merge(
                $request->except('page'),
                ['page' => 1]
            ));
        }

        $paginatedRows = new LengthAwarePaginator(
            $rows->slice(($page - 1) * $perPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => route('projections.students.index'),
                'query' => $request->query(),
            ]
        );

        return view('projections.students.index', [
            'rows' => $paginatedRows,
            'programs' => ResearchStaffProgram::query()
                ->orderBy('name')
                ->orderBy('code')
                ->get(),
            'stageOptions' => $this->students->stageOptions(),
            'selectedProgramId' => $selectedProgramId,
            'selectedStage' => $selectedStage,
            'selectedState' => $selectedState,
            'perPage' => $perPage,
            'summary' => $summary,
        ]);
    }
}
