@extends('tablar::page')

@section('title', 'Gestion de Proyectos')

@section('content')
    <style>
        .project-report-shell {
            display: grid;
            gap: 1.5rem;
        }

        .project-report-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: center;
        }

        .project-report-switch {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .project-report-switch__button {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .project-report-switch__button.is-active {
            background: #0f766e;
            border-color: #0f766e;
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(15, 118, 110, 0.18);
        }

        .project-report-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .project-report-stat {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        .project-report-stat__label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .project-report-stat__value {
            margin-top: 0.35rem;
            font-size: 1.9rem;
            font-weight: 700;
            color: #0f172a;
        }

        .project-report-visual {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
            align-items: start;
        }

        .project-report-panel-wrap {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 20px;
            padding: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.07);
            min-height: 360px;
            display: grid;
        }

        .project-report-panel {
            display: none;
            height: 100%;
        }

        .project-report-panel.is-active {
            display: grid;
        }

        .project-report-donut-wrap {
            display: grid;
            place-items: center;
            gap: 1rem;
            height: 100%;
        }

        .project-report-donut {
            width: 240px;
            height: 240px;
            border-radius: 50%;
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
        }

        .project-report-donut::after {
            content: '';
            position: absolute;
            inset: 48px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
        }

        .project-report-donut__center {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            text-align: center;
            z-index: 1;
            padding: 0 1.5rem;
        }

        .project-report-donut__center strong {
            display: block;
            font-size: 2rem;
            color: #0f172a;
        }

        .project-report-donut__center span {
            color: #64748b;
            font-size: 0.9rem;
        }

        .project-report-columns {
            display: flex;
            align-items: end;
            justify-content: center;
            gap: 1rem;
            min-height: 260px;
            padding: 0 0.5rem;
        }

        .project-report-column {
            flex: 1;
            min-width: 0;
            display: grid;
            gap: 0.75rem;
            justify-items: center;
        }

        .project-report-column__value {
            font-weight: 700;
            color: #0f172a;
        }

        .project-report-column__bar {
            width: min(72px, 100%);
            min-height: 14px;
            border-radius: 18px 18px 0 0;
            display: flex;
            align-items: start;
            justify-content: center;
            padding: 0.5rem 0.35rem 0;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.8rem;
            box-shadow: 0 18px 30px rgba(15, 23, 42, 0.18);
        }

        .project-report-column__label {
            text-align: center;
            font-size: 0.85rem;
            color: #475569;
            line-height: 1.3;
        }

        .project-report-rows {
            display: grid;
            align-content: center;
            gap: 1rem;
        }

        .project-report-row {
            display: grid;
            gap: 0.45rem;
        }

        .project-report-row__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: #334155;
            font-weight: 600;
        }

        .project-report-row__track {
            height: 18px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .project-report-row__fill {
            height: 100%;
            border-radius: inherit;
            min-width: 0;
        }

        .project-report-legend {
            display: grid;
            gap: 0.75rem;
        }

        .project-report-legend__item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: center;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            background: #f8fafc;
        }

        .project-report-legend__swatch {
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 999px;
        }

        .project-report-empty {
            display: grid;
            place-items: center;
            text-align: center;
            color: #64748b;
            min-height: 220px;
        }

        @media (max-width: 991px) {
            .project-report-visual {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575px) {
            .project-report-columns {
                gap: 0.75rem;
            }

            .project-report-column__bar {
                width: 100%;
            }
        }
    </style>

    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Proyectos</li>
                        </ol>
                    </nav>
                    <h2 class="page-title d-flex align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2 text-primary" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 21v-13l8 -4l8 4v13" />
                            <path d="M12 13l8 -4" />
                            <path d="M12 13l-8 -4" />
                            <path d="M12 13v8" />
                            <path d="M8 21h8" />
                        </svg>
                        Gestion de Proyectos
                    </h2>
                    <p class="text-muted mb-0">Consulta tus proyectos y registra nuevas ideas.</p>
                </div>
                @if ($isProfessor || ($isStudent && $enableButtonStudent))
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            @if ($canCreateProject)
                                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Nuevo proyecto
                                </a>
                            @else
                                <button type="button" class="btn btn-primary" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="12" y1="5" x2="12" y2="19" />
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                    </svg>
                                    Nuevo proyecto
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($isProfessor || $isStudent)
                <div class="alert {{ $proposalWindowOpen ? 'alert-info' : 'alert-warning' }} mb-3">
                    <strong>Ventana de propuesta:</strong>
                    @if ($proposalWindowOpen && $proposalWindow)
                        {{ optional($activeAcademicPeriod)->name ?? 'Periodo activo' }} -
                        {{ optional($proposalWindow->start_at)->format('d/m/Y H:i') }} a {{ optional($proposalWindow->end_at)->format('d/m/Y H:i') }}.
                    @else
                        {{ $proposalWindowMessage }}
                    @endif
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Buscar proyectos</h3>
                    @if ($isResearchStaff)
                        <a href="{{ route('projects.index', array_merge(request()->except('page'), ['pending_review_due_to_age' => 1])) }}" class="btn btn-outline-warning btn-sm">
                            Ver pendientes por antiguedad
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        @if ($isResearchStaff)
                            <input type="hidden" name="report_key" value="{{ $reportFilters['report_key'] }}">
                            <input type="hidden" name="report_search" value="{{ $reportFilters['report_search'] }}">
                            <input type="hidden" name="report_from" value="{{ $reportFilters['report_from'] }}">
                            <input type="hidden" name="report_to" value="{{ $reportFilters['report_to'] }}">
                            <input type="hidden" name="report_program_id" value="{{ $reportFilters['report_program_id'] }}">
                        @endif
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="search" class="form-label">Titulo</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <circle cx="10" cy="10" r="7" />
                                        <line x1="21" y1="21" x2="15" y2="15" />
                                    </svg>
                                </span>
                                <input type="search" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Titulo del proyecto">
                            </div>
                        </div>
                        @if ($isResearchStaff)
                            <div class="col-12 col-md-6 col-lg-4">
                                <label for="city_program_id" class="form-label">Programa - Ciudad</label>
                                <select name="city_program_id" id="city_program_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    @foreach ($cityPrograms as $cp)
                                        <option value="{{ $cp->id }}" {{ (string) $selectedCityProgram === (string) $cp->id ? 'selected' : '' }}>
                                            {{ $cp->program->name }} - {{ $cp->city->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-12 col-md-6 col-lg-4">
                            <label for="status_id" class="form-label">Estado</label>
                            <select name="status_id" id="status_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                @foreach ($projectStatuses as $status)
                                    <option value="{{ $status->id }}" {{ (string) $selectedStatus === (string) $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($isResearchStaff)
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label d-block">Alertas de revision</label>
                                <label class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input" name="pending_review_due_to_age" value="1" {{ $pendingReviewDueToAge ? 'checked' : '' }}>
                                    <span class="form-check-label">Mostrar solo pendientes de revision por antiguedad</span>
                                </label>
                            </div>
                        @endif
                        <div class="col-12 col-md-4 col-lg-2">
                            <button type="submit" class="btn btn-primary w-100">Aplicar filtros</button>
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Listado de proyectos</h3>
                    <div class="card-actions">
                        <span class="badge bg-azure">{{ $projects->total() }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter align-middle">
                        <thead>
                            <tr>
                                <th class="w-1">ID</th>
                                <th>Titulo</th>
                                <th>Area tematica</th>
                                <th>Estado</th>
                                <th>Profesores</th>
                                <th>Estudiantes</th>
                                <th class="w-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                <tr>
                                    <td>{{ $project->id }}</td>
                                    <td class="text-break">{{ $project->title }}</td>
                                    <td>{{ $project->thematicArea->name ?? 'Sin area' }}</td>
                                    @php
                                        $statusName = $project->projectStatus->name ?? 'Sin estado';
                                        $normalizedStatus = \Illuminate\Support\Str::of($statusName)->ascii()->lower()->squish()->toString();
                                        $statusClasses = [
                                            'pendiente de aprobacion' => 'bg-warning text-dark',
                                            'devuelto para correccion' => 'bg-danger text-white',
                                            'aprobado' => 'bg-success text-white',
                                            'waiting evaluation' => 'bg-primary text-white',
                                        ];
                                        $badgeClass = $statusClasses[$normalizedStatus] ?? 'bg-secondary text-white';
                                    @endphp
                                    <td>
                                        <div class="d-flex flex-column gap-1 align-items-start">
                                            <span class="badge {{ $badgeClass }}">{{ $statusName }}</span>
                                            @if ($project->pending_review_due_to_age)
                                                <span class="badge bg-orange-lt text-orange">Pendiente de revision por antiguedad</span>
                                                @if ($project->elapsed_periods_since_proposal !== null)
                                                    <small class="text-secondary">{{ $project->elapsed_periods_since_proposal }} periodos transcurridos</small>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @forelse ($project->professors as $professor)
                                            <div>{{ $professor->name }} {{ $professor->last_name }}</div>
                                        @empty
                                            <span class="text-secondary">Sin profesores</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @forelse ($project->students as $student)
                                            <div>{{ $student->name }} {{ $student->last_name }}</div>
                                        @empty
                                            <span class="text-secondary">Sin estudiantes</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">Ver</a>
                                            @if ($normalizedStatus === 'devuelto para correccion' && ! $isResearchStaff)
                                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary btn-sm">
                                                    Editar
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary">No se encontraron proyectos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex flex-column flex-lg-row align-items-center justify-content-between gap-2">
                    <div class="text-secondary mb-2 mb-lg-0">Mostrando {{ $projects->firstItem() ?? 0 }} a {{ $projects->lastItem() ?? 0 }} de {{ $projects->total() }} registros</div>
                    @if ($projects->hasPages())
                        {{ $projects->links('vendor.pagination.bootstrap-5-numeric') }}
                    @endif
                </div>
            </div>

            @if ($isResearchStaff)
                @php
                    $topReportSegment = collect($reportSegments)->sortByDesc('value')->first();
                    $maxReportValue = max($reportData['values'] ?: [0]);
                    $currentPercent = 0;
                    $chartStops = [];

                    foreach ($reportSegments as $segment) {
                        $start = $currentPercent;
                        $currentPercent = min(100, $currentPercent + $segment['percentage']);
                        $chartStops[] = "{$segment['color']} {$start}% {$currentPercent}%";
                    }

                    $chartBackground = $chartStops !== []
                        ? 'conic-gradient(' . implode(', ', $chartStops) . ')'
                        : 'linear-gradient(135deg, #d1d5db, #9ca3af)';
                @endphp

                <div class="card mt-3" id="projects-report">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Reporte de proyectos</h3>
                            <div class="text-muted">{{ $reportModules[$activeReportKey]['description'] ?? 'Distribucion de proyectos.' }}</div>
                        </div>
                    </div>
                    <div class="card-body project-report-shell">
                        <form method="GET" action="{{ route('projects.index') }}#projects-report" class="row g-3 align-items-end">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="status_id" value="{{ $selectedStatus }}">
                            <input type="hidden" name="city_program_id" value="{{ $selectedCityProgram }}">
                            <input type="hidden" name="pending_review_due_to_age" value="{{ $pendingReviewDueToAge ? 1 : '' }}">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="report_key" class="form-label">Que deseas comparar</label>
                                <select id="report_key" name="report_key" class="form-select">
                                    @foreach ($reportModules as $reportKey => $module)
                                        <option value="{{ $reportKey }}" @selected($activeReportKey === $reportKey)>
                                            {{ $module['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="report_search" class="form-label">Buscar dato</label>
                                <input
                                    type="text"
                                    id="report_search"
                                    name="report_search"
                                    class="form-control"
                                    placeholder="Estado, area, linea, tipo o titulo"
                                    value="{{ $reportFilters['report_search'] }}"
                                >
                            </div>
                            <div class="col-12 col-md-4 col-lg-2">
                                <label for="report_from" class="form-label">Desde</label>
                                <input
                                    type="date"
                                    id="report_from"
                                    name="report_from"
                                    class="form-control"
                                    value="{{ $reportFilters['report_from'] }}"
                                >
                            </div>
                            <div class="col-12 col-md-4 col-lg-2">
                                <label for="report_to" class="form-label">Hasta</label>
                                <input
                                    type="date"
                                    id="report_to"
                                    name="report_to"
                                    class="form-control"
                                    value="{{ $reportFilters['report_to'] }}"
                                >
                            </div>
                            <div class="col-12 col-md-4 col-lg-2">
                                <label for="report_program_id" class="form-label">Programa</label>
                                <select id="report_program_id" name="report_program_id" class="form-select">
                                    <option value="">Todos los programas</option>
                                    @foreach ($reportProgramOptions as $program)
                                        <option value="{{ $program->id }}" @selected((int) $reportFilters['report_program_id'] === (int) $program->id)>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">Generar reporte</button>
                                    <a href="{{ route('projects.index') }}#projects-report" class="btn btn-outline-secondary">Limpiar</a>
                                    <a
                                        href="{{ route('projects.index', array_filter([
                                            'search' => $search,
                                            'status_id' => $selectedStatus,
                                            'city_program_id' => $selectedCityProgram,
                                            'pending_review_due_to_age' => $pendingReviewDueToAge ? 1 : null,
                                            'report_key' => $activeReportKey,
                                            'report_search' => $reportFilters['report_search'],
                                            'report_from' => $reportFilters['report_from'],
                                            'report_to' => $reportFilters['report_to'],
                                            'report_program_id' => $reportFilters['report_program_id'],
                                            'report_export' => 'csv',
                                        ], static fn ($value) => $value !== null && $value !== '')) }}"
                                        class="btn btn-outline-primary"
                                    >
                                        Exportar CSV
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class="project-report-toolbar">
                            <div class="project-report-switch" role="tablist" aria-label="Tipos de grafico del reporte">
                                <button type="button" class="project-report-switch__button is-active" data-chart-target="donut">Dona</button>
                                <button type="button" class="project-report-switch__button" data-chart-target="columns">Barras verticales</button>
                                <button type="button" class="project-report-switch__button" data-chart-target="rows">Barras horizontales</button>
                            </div>
                            <div class="text-muted small">Puedes cambiar la visualizacion sin regenerar el reporte.</div>
                        </div>

                        @if (! empty($reportFilters['report_search']))
                            <div class="alert alert-secondary mb-0">
                                Busqueda aplicada: <strong>{{ $reportFilters['report_search'] }}</strong>
                            </div>
                        @endif

                        <div class="project-report-grid">
                            <div class="project-report-stat">
                                <div class="project-report-stat__label">Total de registros</div>
                                <div class="project-report-stat__value">{{ $reportData['total'] }}</div>
                            </div>
                            <div class="project-report-stat">
                                <div class="project-report-stat__label">Categorias detectadas</div>
                                <div class="project-report-stat__value">{{ count($reportData['categories']) }}</div>
                            </div>
                            <div class="project-report-stat">
                                <div class="project-report-stat__label">Categoria principal</div>
                                <div class="project-report-stat__value" style="font-size: 1.2rem;">
                                    {{ $topReportSegment['label'] ?? 'Sin datos' }}
                                </div>
                            </div>
                        </div>

                        <div class="project-report-visual">
                            <div class="project-report-panel-wrap">
                                <div class="project-report-panel project-report-panel--donut is-active" data-chart-panel="donut">
                                    @if ($reportSegments !== [])
                                        <div class="project-report-donut-wrap">
                                            <div class="project-report-donut" style="background: {{ $chartBackground }};">
                                                <div class="project-report-donut__center">
                                                    <div>
                                                        <strong>{{ $reportData['total'] }}</strong>
                                                        <span>Total de proyectos</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-muted text-center">
                                                Diagrama de dona generado con los filtros seleccionados.
                                            </div>
                                        </div>
                                    @else
                                        <div class="project-report-empty">Sin datos para construir el grafico.</div>
                                    @endif
                                </div>

                                <div class="project-report-panel" data-chart-panel="columns">
                                    @if ($reportSegments !== [])
                                        <div class="project-report-columns">
                                            @foreach ($reportSegments as $segment)
                                                @php
                                                    $columnHeight = $maxReportValue > 0
                                                        ? max(14, (int) round(($segment['value'] / $maxReportValue) * 220))
                                                        : 14;
                                                @endphp
                                                <div class="project-report-column">
                                                    <div class="project-report-column__value">{{ $segment['value'] }}</div>
                                                    <div
                                                        class="project-report-column__bar"
                                                        style="height: {{ $columnHeight }}px; background: {{ $segment['color'] }};"
                                                        title="{{ $segment['label'] }}: {{ $segment['value'] }}"
                                                    >
                                                        {{ number_format($segment['percentage'], 1) }}%
                                                    </div>
                                                    <div class="project-report-column__label">{{ $segment['label'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="project-report-empty">Sin datos para construir el grafico.</div>
                                    @endif
                                </div>

                                <div class="project-report-panel" data-chart-panel="rows">
                                    @if ($reportSegments !== [])
                                        <div class="project-report-rows">
                                            @foreach ($reportSegments as $segment)
                                                @php
                                                    $rowWidth = $maxReportValue > 0
                                                        ? round(($segment['value'] / $maxReportValue) * 100, 2)
                                                        : 0;
                                                @endphp
                                                <div class="project-report-row">
                                                    <div class="project-report-row__header">
                                                        <span>{{ $segment['label'] }}</span>
                                                        <span>{{ $segment['value'] }} registros</span>
                                                    </div>
                                                    <div class="project-report-row__track">
                                                        <div
                                                            class="project-report-row__fill"
                                                            style="width: {{ $rowWidth }}%; background: {{ $segment['color'] }};"
                                                            title="{{ $segment['label'] }}: {{ number_format($segment['percentage'], 2) }}%"
                                                        ></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="project-report-empty">Sin datos para construir el grafico.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="project-report-legend">
                                @forelse ($reportSegments as $segment)
                                    <div class="project-report-legend__item">
                                        <span class="project-report-legend__swatch" style="background: {{ $segment['color'] }}"></span>
                                        <div>
                                            <div class="fw-semibold">{{ $segment['label'] }}</div>
                                            <div class="text-muted small">{{ $segment['value'] }} registros</div>
                                        </div>
                                        <div class="fw-semibold">{{ number_format($segment['percentage'], 2) }}%</div>
                                    </div>
                                @empty
                                    <div class="text-muted">Sin datos para construir la leyenda del reporte.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if ($isResearchStaff)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const buttons = Array.from(document.querySelectorAll('[data-chart-target]'));
                const panels = Array.from(document.querySelectorAll('[data-chart-panel]'));

                if (buttons.length === 0 || panels.length === 0) {
                    return;
                }

                buttons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const target = button.getAttribute('data-chart-target');

                        buttons.forEach(function (item) {
                            item.classList.toggle('is-active', item === button);
                        });

                        panels.forEach(function (panel) {
                            panel.classList.toggle('is-active', panel.getAttribute('data-chart-panel') === target);
                        });
                    });
                });
            });
        </script>
    @endif
@endsection
