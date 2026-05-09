<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicPeriodRequest;
use App\Models\AcademicPeriod;
use App\Models\ResearchStaff\ResearchStaffAcademicPeriod;
use App\Services\Students\StudentAcademicProgressService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search'));
        $status = $request->get('status');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $academicPeriods = ResearchStaffAcademicPeriod::query()
            ->withCount('processWindows')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('start_date')
            ->paginate($perPage)
            ->appends($request->query());

        return view('academic-periods.index', [
            'academicPeriods' => $academicPeriods,
            'statusOptions' => AcademicPeriod::statusOptions(),
            'search' => $search,
            'status' => $status,
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        return view('academic-periods.create', [
            'academicPeriod' => new ResearchStaffAcademicPeriod(),
            'statusOptions' => AcademicPeriod::statusOptions(),
            'dateConstraints' => ResearchStaffAcademicPeriod::formDateConstraints(),
        ]);
    }

    public function store(AcademicPeriodRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            if (($data['status'] ?? null) === AcademicPeriod::STATUS_ACTIVE) {
                ResearchStaffAcademicPeriod::query()->where('is_active', true)->update([
                    'is_active' => false,
                    'status' => AcademicPeriod::STATUS_CLOSED,
                ]);
            }

            $data['is_active'] = ($data['status'] ?? null) === AcademicPeriod::STATUS_ACTIVE;

            $period = ResearchStaffAcademicPeriod::query()->create($data);

            if ($period->is_active && $period->status === AcademicPeriod::STATUS_ACTIVE) {
                app(StudentAcademicProgressService::class)->syncAll($period);
            }

            Log::info('Periodo academico creado', [
                'academic_period_id' => $period->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('academic-periods.index')->with('success', 'Periodo academico creado correctamente.');
        });
    }

    public function show(ResearchStaffAcademicPeriod $academic_period): View
    {
        $academic_period->load(['processWindows' => fn ($query) => $query->orderBy('start_at')]);

        return view('academic-periods.show', [
            'academicPeriod' => $academic_period,
            'statusOptions' => AcademicPeriod::statusOptions(),
            'processOptions' => \App\Models\AcademicProcessWindow::processOptions(),
        ]);
    }

    public function edit(ResearchStaffAcademicPeriod $academic_period): View
    {
        return view('academic-periods.edit', [
            'academicPeriod' => $academic_period,
            'statusOptions' => AcademicPeriod::statusOptions(),
            'dateConstraints' => ResearchStaffAcademicPeriod::formDateConstraints($academic_period),
        ]);
    }

    public function update(AcademicPeriodRequest $request, ResearchStaffAcademicPeriod $academic_period): RedirectResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($academic_period, $data) {
            if (($data['status'] ?? null) === AcademicPeriod::STATUS_ACTIVE) {
                ResearchStaffAcademicPeriod::query()
                    ->where('id', '!=', $academic_period->id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'status' => AcademicPeriod::STATUS_CLOSED,
                    ]);
            }

            $data['is_active'] = ($data['status'] ?? null) === AcademicPeriod::STATUS_ACTIVE;

            $academic_period->update($data);

            if ($academic_period->is_active && $academic_period->status === AcademicPeriod::STATUS_ACTIVE) {
                app(StudentAcademicProgressService::class)->syncAll($academic_period);
            }

            return redirect()->route('academic-periods.index')->with('success', 'Periodo academico actualizado correctamente.');
        });
    }

    public function destroy(ResearchStaffAcademicPeriod $academic_period): RedirectResponse
    {
        try {
            $academic_period->delete();

            return redirect()->route('academic-periods.index')->with('success', 'Periodo academico eliminado correctamente.');
        } catch (QueryException $exception) {
            return redirect()->route('academic-periods.index')->with('error', 'No se pudo eliminar el periodo porque tiene informacion asociada.');
        }
    }

    public function activate(ResearchStaffAcademicPeriod $academic_period): RedirectResponse
    {
        if (! $academic_period->canBeActivatedOn(now())) {
            return redirect()
                ->route('academic-periods.index')
                ->with('error', 'No se puede activar el periodo porque la fecha actual está fuera del rango entre inicio y cierre del periodo.');
        }

        return DB::transaction(function () use ($academic_period) {
            ResearchStaffAcademicPeriod::query()->where('is_active', true)->update([
                'is_active' => false,
                'status' => AcademicPeriod::STATUS_CLOSED,
            ]);

            $academic_period->update([
                'is_active' => true,
                'status' => AcademicPeriod::STATUS_ACTIVE,
            ]);

            app(StudentAcademicProgressService::class)->syncAll($academic_period);

            return redirect()->route('academic-periods.index')->with('success', 'Periodo academico activado correctamente.');
        });
    }

    public function close(ResearchStaffAcademicPeriod $academic_period): RedirectResponse
    {
        $academic_period->update([
            'is_active' => false,
            'status' => AcademicPeriod::STATUS_CLOSED,
        ]);

        return redirect()->route('academic-periods.index')->with('success', 'Periodo academico cerrado correctamente.');
    }
}
