@extends('tablar::page')

@section('title', 'Proyecciones - Docentes')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item">Proyecciones</li>
                            <li class="breadcrumb-item active" aria-current="page">Docentes</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Soporte de docentes</h2>
                    <p class="text-muted mb-0">Consulta horas asignadas, ideas esperadas, ideas registradas y faltantes por docente.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('projections.professors.index') }}" class="row g-3 align-items-end">
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

            <div class="row row-deck row-cards mb-3">
                <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Docentes con carga</div><div class="h1 mb-0">{{ $summary['teachers'] }}</div></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Horas asignadas</div><div class="h1 mb-0">{{ $summary['assigned_hours'] }}</div></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Ideas registradas</div><div class="h1 mb-0">{{ $summary['registered_ideas'] }}</div></div></div></div>
                <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Ideas faltantes</div><div class="h1 mb-0">{{ $summary['missing_ideas'] }}</div></div></div></div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Programa</th>
                                <th>Estado</th>
                                <th>Horas</th>
                                <th>Ideas esperadas</th>
                                <th>Ideas registradas</th>
                                <th>Ideas faltantes</th>
                                <th>Historico</th>
                                <th class="text-center">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ trim(($assignment->professor?->name ?? '') . ' ' . ($assignment->professor?->last_name ?? '')) }}</div>
                                        <div class="text-muted small">{{ $assignment->professor?->cityProgram?->city?->name }}</div>
                                    </td>
                                    <td>{{ $assignment->program?->name }}</td>
                                    <td>
                                        <span class="badge {{ $assignment->professor_active ? 'bg-green-lt' : 'bg-red-lt' }}">
                                            {{ $assignment->professor_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $assignment->assigned_hours }}</td>
                                    <td>{{ $assignment->expected_ideas }}</td>
                                    <td>{{ $assignment->registered_ideas }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $assignment->missing_ideas }}</span>
                                        @if($assignment->idea_balance > 0)
                                            <div class="text-success small">Supera la expectativa en {{ $assignment->idea_balance }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->historical_registered_ideas }} ideas</td>
                                    <td class="text-center">
                                        <a href="{{ route('projections.teacher-assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-primary">Editar asignacion</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No hay asignaciones docentes para los filtros seleccionados.</td>
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
