@extends('tablar::page')

@section('title', 'Proyecciones - Estudiantes')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item">Proyecciones</li>
                            <li class="breadcrumb-item active" aria-current="page">Estudiantes</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Soporte de estudiantes PG1 / PG2</h2>
                    <p class="text-muted mb-0">Vista administrativa para entender la base estudiantil que alimenta la proyeccion de continuidad a PG2.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('projections.students.index') }}" class="row g-3 align-items-end">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <div class="col-12 col-lg-3">
                            <label for="program_id" class="form-label">Programa</label>
                            <select id="program_id" name="program_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ (int) $selectedProgramId === (int) $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}{{ $program->code ? ' (' . $program->code . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-2">
                            <label for="stage" class="form-label">Etapa PG</label>
                            <select id="stage" name="stage" class="form-select">
                                <option value="">Todas</option>
                                @foreach($stageOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedStage === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label for="state" class="form-label">Estado usuario</label>
                            <select id="state" name="state" class="form-select">
                                <option value="">Todos</option>
                                <option value="1" {{ (string) $selectedState === '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ (string) $selectedState === '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-2 d-grid">
                            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                        </div>
                        <div class="col-12 col-lg-2 d-grid">
                            <a href="{{ route('projections.students.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-deck row-cards mb-3">
                <div class="col-md-3">
                    <div class="card"><div class="card-body"><div class="text-muted">Estudiantes filtrados</div><div class="h1 mb-0">{{ $summary['total_students'] }}</div></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body"><div class="text-muted">Activos</div><div class="h1 mb-0">{{ $summary['active_students'] }}</div></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body"><div class="text-muted">PG1 actual</div><div class="h1 mb-0">{{ $summary['pg1_students'] }}</div></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body"><div class="text-muted">PG2 actual</div><div class="h1 mb-0">{{ $summary['pg2_students'] }}</div></div></div>
                </div>
                <div class="col-md-3">
                    <div class="card"><div class="card-body"><div class="text-muted">Proyeccion automatica a PG2</div><div class="h1 mb-0">{{ $summary['projected_pg2_students'] }}</div></div></div>
                </div>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Programa</th>
                                <th>Estado</th>
                                <th>Etapa PG</th>
                                <th>Proyecto actual</th>
                                <th>Periodo base</th>
                                <th>Docentes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row['full_name'] }}</div>
                                        <div class="text-muted small">Documento: {{ $row['card_id'] }} | Semestre: {{ $row['semester'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $row['program_name'] }}</div>
                                        <div class="text-muted small">{{ $row['city_name'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $row['is_active'] ? 'bg-green-lt' : 'bg-red-lt' }}">
                                            {{ $row['is_active'] ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row['pg_stage_label'] }}</div>
                                        <div class="text-muted small">{{ $row['progression_note'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $row['project_title'] ?: 'Sin proyecto' }}</div>
                                        <div class="text-muted small">{{ $row['project_status'] ?: 'Sin estado' }}</div>
                                    </td>
                                    <td>{{ $row['assignment_period_name'] ?: 'No aplica' }}</td>
                                    <td class="text-muted">{{ $row['teacher_names'] ?: 'Sin docentes asociados' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No hay estudiantes para los filtros seleccionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($rows->hasPages())
                    <div class="card-footer">
                        {{ $rows->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
