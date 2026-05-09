@extends('tablar::page')

@section('title', 'Asignacion docente')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item">Proyecciones</li>
                            <li class="breadcrumb-item active" aria-current="page">Asignacion docente</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Asignacion docente</h2>
                    <p class="text-muted mb-0">Registra las horas oficiales de direccion de proyectos por programa y periodo.</p>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('projections.teacher-assignments.create') }}" class="btn btn-primary {{ $targetPeriod ? '' : 'disabled' }}">
                        Registrar asignacion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @unless($targetPeriod)
                <div class="alert alert-warning">
                    Debes tener un periodo academico activo y el siguiente periodo creado para registrar asignaciones docentes.
                </div>
            @else
                <div class="alert alert-info">
                    Periodo operativo actual: <strong>{{ $targetPeriod->name }}</strong>.
                </div>
            @endunless

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('projections.teacher-assignments.index') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="academic_period_id" class="form-label">Periodo</label>
                            <select id="academic_period_id" name="academic_period_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}" {{ (int) $selectedPeriodId === (int) $period->id ? 'selected' : '' }}>
                                        {{ $period->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label for="program_id" class="form-label">Programa</label>
                            <select id="program_id" name="program_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ (int) $selectedProgramId === (int) $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="per_page" class="form-label">Registros</label>
                            <select id="per_page" name="per_page" class="form-select">
                                @foreach([10, 25, 50] as $size)
                                    <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-lg-2 d-grid">
                            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Programa</th>
                                <th>Docente</th>
                                <th>Horas</th>
                                <th>Ideas esperadas</th>
                                <th>Observaciones</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->academicPeriod?->name }}</td>
                                    <td>{{ $assignment->program?->name }}</td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ trim(($assignment->professor?->name ?? '') . ' ' . ($assignment->professor?->last_name ?? '')) }}
                                        </div>
                                        <div class="text-muted small">{{ $assignment->professor?->cityProgram?->city?->name }}</div>
                                    </td>
                                    <td>{{ $assignment->assigned_hours }}</td>
                                    <td>{{ $assignment->expected_ideas }}</td>
                                    <td class="text-muted">{{ $assignment->observations ?: 'Sin observaciones.' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('projections.teacher-assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-primary">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty">
                                            <p class="empty-title">No hay asignaciones registradas</p>
                                            <p class="empty-subtitle text-muted">Registra la asignacion oficial de horas por docente y programa.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($assignments->hasPages())
                    <div class="card-footer">
                        {{ $assignments->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
