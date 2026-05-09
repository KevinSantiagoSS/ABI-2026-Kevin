@extends('tablar::page')

@section('title', 'Demanda de ideas')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item">Proyecciones</li>
                            <li class="breadcrumb-item active" aria-current="page">Demanda de ideas</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Demanda de ideas</h2>
                    <p class="text-muted mb-0">Compara la demanda PG1 proyectada con las ideas aprobadas y sin asignar disponibles en el banco.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('projections.idea-demand.index') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-lg-5">
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
                        <div class="col-12 col-lg-5">
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
                        <div class="col-12 col-lg-2 d-grid">
                            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row row-deck row-cards mb-3">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Programas proyectados</div>
                            <div class="h1 mb-0">{{ $summary['totals']['projected_programs'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Ideas requeridas</div>
                            <div class="h1 mb-0">{{ $summary['totals']['needed_ideas'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Ideas disponibles</div>
                            <div class="h1 mb-0">{{ $summary['totals']['available_ideas'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted">Ideas faltantes</div>
                            <div class="h1 mb-0">{{ $summary['totals']['missing_ideas'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Programa</th>
                                <th>Periodo</th>
                                <th>Ideas requeridas</th>
                                <th>Ideas disponibles</th>
                                <th>Faltantes</th>
                                <th>Excedente</th>
                                <th>Alertas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['rows'] as $row)
                                <tr>
                                    <td>{{ $row['program']?->name }}</td>
                                    <td>{{ $row['academic_period']?->name }}</td>
                                    <td>{{ $row['needed_ideas'] }}</td>
                                    <td>{{ $row['available_ideas'] }}</td>
                                    <td>{{ $row['missing_ideas'] }}</td>
                                    <td>{{ $row['excess_ideas'] }}</td>
                                    <td>
                                        @if(empty($row['alerts']))
                                            <span class="text-muted">Sin alertas</span>
                                        @else
                                            @foreach(array_slice($row['alerts'], 0, 2) as $alert)
                                                <div class="small text-{{ $alert['level'] === 'danger' ? 'danger' : ($alert['level'] === 'warning' ? 'warning' : 'muted') }}">
                                                    {{ $alert['message'] }}
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty">
                                            <p class="empty-title">No hay informacion para analizar</p>
                                            <p class="empty-subtitle text-muted">Primero registra proyecciones de carga para el periodo objetivo.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($detailRow)
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Balance por linea</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Linea de investigacion</th>
                                            <th>Ideas disponibles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailRow['line_breakdown'] as $line)
                                            <tr>
                                                <td>{{ $line['name'] }}</td>
                                                <td>{{ $line['count'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted">Sin ideas aprobadas disponibles para este programa.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Balance por area tematica</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Area</th>
                                            <th>Linea</th>
                                            <th>Ideas disponibles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailRow['area_breakdown'] as $area)
                                            <tr>
                                                <td>{{ $area['name'] }}</td>
                                                <td>{{ $area['line_name'] }}</td>
                                                <td>{{ $area['count'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-muted">Sin datos de areas para este programa.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Alertas de equilibrio</h3>
                            </div>
                            <div class="card-body">
                                @forelse($detailRow['alerts'] as $alert)
                                    <div class="alert alert-{{ $alert['level'] === 'danger' ? 'danger' : ($alert['level'] === 'warning' ? 'warning' : ($alert['level'] === 'success' ? 'success' : 'secondary')) }} mb-2">
                                        {{ $alert['message'] }}
                                    </div>
                                @empty
                                    <div class="text-muted">No se generaron alertas para el programa seleccionado.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
