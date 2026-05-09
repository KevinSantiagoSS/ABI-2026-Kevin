<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicProcessWindowRequest;
use App\Models\AcademicProcessWindow;
use App\Models\ResearchStaff\ResearchStaffAcademicPeriod;
use App\Models\ResearchStaff\ResearchStaffAcademicProcessWindow;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicProcessWindowController extends Controller
{
    public function index(Request $request): View
    {
        $academicPeriodId = $request->get('academic_period_id');
        $processKey = $request->get('process_key');
        $status = $request->get('status');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $now = now();

        $windows = ResearchStaffAcademicProcessWindow::query()
            ->with('academicPeriod')
            ->when($academicPeriodId, fn ($query) => $query->where('academic_period_id', $academicPeriodId))
            ->when($processKey, fn ($query) => $query->where('process_key', $processKey))
            ->when($status === 'scheduled', fn ($query) => $query->where('start_at', '>', $now))
            ->when($status === 'active', fn ($query) => $query->where('start_at', '<=', $now)->where('end_at', '>=', $now))
            ->when($status === 'closed', fn ($query) => $query->where('end_at', '<', $now))
            ->orderByDesc('start_at')
            ->paginate($perPage)
            ->appends($request->query());

        return view('academic-process-windows.index', [
            'windows' => $windows,
            'periods' => ResearchStaffAcademicPeriod::query()->orderByDesc('start_date')->pluck('name', 'id'),
            'processOptions' => AcademicProcessWindow::processOptions(),
            'academicPeriodId' => $academicPeriodId,
            'processKey' => $processKey,
            'status' => $status,
            'perPage' => $perPage,
        ]);
    }

    public function create(Request $request): View
    {
        $window = new ResearchStaffAcademicProcessWindow();
        $window->academic_period_id = $request->integer('academic_period_id');

        return view('academic-process-windows.create', [
            'window' => $window,
            'periods' => ResearchStaffAcademicPeriod::query()->orderByDesc('start_date')->pluck('name', 'id'),
            'processOptions' => AcademicProcessWindow::processOptions(),
        ]);
    }

    public function store(AcademicProcessWindowRequest $request): RedirectResponse
    {
        ResearchStaffAcademicProcessWindow::query()->create($request->validated());

        return redirect()
            ->route('academic-process-windows.index')
            ->with('success', 'Ventana de calendario creada correctamente.');
    }

    public function show(ResearchStaffAcademicProcessWindow $academic_process_window): View
    {
        $academic_process_window->load('academicPeriod');

        return view('academic-process-windows.show', [
            'window' => $academic_process_window,
            'processOptions' => AcademicProcessWindow::processOptions(),
        ]);
    }

    public function edit(ResearchStaffAcademicProcessWindow $academic_process_window): View
    {
        return view('academic-process-windows.edit', [
            'window' => $academic_process_window,
            'periods' => ResearchStaffAcademicPeriod::query()->orderByDesc('start_date')->pluck('name', 'id'),
            'processOptions' => AcademicProcessWindow::processOptions(),
        ]);
    }

    public function update(AcademicProcessWindowRequest $request, ResearchStaffAcademicProcessWindow $academic_process_window): RedirectResponse
    {
        $academic_process_window->update($request->validated());

        return redirect()
            ->route('academic-process-windows.index')
            ->with('success', 'Ventana de calendario actualizada correctamente.');
    }

    public function destroy(ResearchStaffAcademicProcessWindow $academic_process_window): RedirectResponse
    {
        try {
            $academic_process_window->delete();

            return redirect()
                ->route('academic-process-windows.index')
                ->with('success', 'Ventana de calendario eliminada correctamente.');
        } catch (QueryException $exception) {
            return redirect()
                ->route('academic-process-windows.index')
                ->with('error', 'No se pudo eliminar la ventana de calendario.');
        }
    }
}
