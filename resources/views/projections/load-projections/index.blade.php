@extends('tablar::page')

@section('title', 'Proyeccion de carga')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item">Proyecciones</li>
                            <li class="breadcrumb-item active" aria-current="page">Proyeccion de carga</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Proyeccion de carga</h2>
                    <p class="text-muted mb-0">
                        Registra y consulta la carga proyectada de direccion de proyectos para el siguiente periodo academico.
                    </p>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('projections.load-projections.create') }}" class="btn btn-primary {{ $targetPeriod ? '' : 'disabled' }}">
                        Registrar proyeccion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if(config('tablar.display_alert'))
                @include('tablar::common.alert')
            @endif

            @unless($targetPeriod)
                <div class="alert alert-warning">
                    Debes tener un periodo academico activo y el siguiente periodo creado para registrar proyecciones.
                </div>
            @else
                <div class="alert alert-info">
                    Periodo objetivo actual: <strong>{{ $targetPeriod->name }}</strong>.
                </div>
            @endunless

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('projections.load-projections.index') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="academic_period_id" class="form-label">Periodo objetivo</label>
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
                                <th>PG1</th>
                                <th>PG2</th>
                                <th>Horas semanales</th>
                                <th>Observaciones</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projections as $projection)
                                <tr>
                                    <td>{{ $projection->academicPeriod?->name }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $projection->program?->name }}</div>
                                        <div class="text-muted small">{{ $projection->program?->researchGroup?->name }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $projection->projected_pg1_students }} estudiantes</div>
                                        <div class="text-muted small">{{ $projection->projected_pg1_groups }} grupos</div>
                                    </td>
                                    <td>
                                        <div>{{ $projection->projected_pg2_students }} estudiantes</div>
                                        <div class="text-muted small">{{ $projection->projected_pg2_groups }} grupos</div>
                                    </td>
                                    <td>
                                        <div>PG1: {{ $projection->pg1_weekly_hours }}</div>
                                        <div>PG2: {{ $projection->pg2_weekly_hours }}</div>
                                        <div class="fw-semibold">Total: {{ $projection->total_weekly_hours }}</div>
                                    </td>
                                    <td class="text-muted">{{ $projection->observations ?: 'Sin observaciones.' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('projections.load-projections.edit', $projection) }}" class="btn btn-sm btn-outline-primary">
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty">
                                            <p class="empty-title">No hay proyecciones registradas</p>
                                            <p class="empty-subtitle text-muted">Registra la carga proyectada por programa para el periodo objetivo.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($projections->hasPages())
                    <div class="card-footer">
                        {{ $projections->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
