{{-- 
    View path: resources/views/project-versions/index.blade.php
    Purpose: Displays the version history list for a specific project, including summary information and pagination.
    This view allows users to review previously generated versions and access their details.
    Dynamic variables used: $project (project information), $versions (paginated list of versions), $totalVersions (total count of versions).
    Included partials or components: tablar::common.alert, pagination::bootstrap-5.
    All markup below follows Tablar styling conventions for visual consistency.
--}}
@extends('tablar::page')

@section('title', 'Historial de versiones')

@section('content')
    {{-- Page header with breadcrumb navigation --}}
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    {{-- Breadcrumb navigation --}}
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                {{-- Link to home --}}
                                <a href="{{ route('home') }}">Inicio</a>
                            </li>
                            <li class="breadcrumb-item">
                                {{-- Link to projects list --}}
                                <a href="{{ route('projects.index') }}">Proyectos</a>
                            </li>
                            <li class="breadcrumb-item">
                                {{-- Link to current project --}}
                                <a href="{{ route('projects.show', $project) }}">Proyecto #{{ $project->id }}</a>
                            </li>
                            {{-- Current page --}}
                            <li class="breadcrumb-item active" aria-current="page">Historial</li>
                        </ol>
                    </nav>

                    {{-- Page title with total versions --}}
                    <h2 class="page-title d-flex align-items-center">
                        {{-- Icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg me-2 text-primary" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 8l0 4l2 2" />
                            <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                        </svg>
                        Historial de versiones

                        {{-- Total versions badge --}}
                        <span class="badge bg-primary ms-2">{{ $totalVersions }}</span>
                    </h2>

                    {{-- Description --}}
                    <p class="text-muted mb-0">
                        Consulta las versiones registradas para 
                        <strong>{{ $project->title }}</strong>.
                    </p>
                </div>

                {{-- Back button --}}
                <div class="col-auto ms-auto d-print-none">
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                        Volver al proyecto
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="page-body">
        <div class="container-xl">

            {{-- Display alert messages if enabled --}}
            @if(config('tablar.display_alert'))
                @include('tablar::common.alert')
            @endif

            {{-- Card: Current project summary --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Project title --}}
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-secondary small">Titulo actual</div>
                            <div class="fw-semibold">{{ $project->title }}</div>
                        </div>

                        {{-- Project status --}}
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-secondary small">Estado actual</div>
                            <div class="fw-semibold">
                                {{ $project->projectStatus->name ?? 'Sin estado' }}
                            </div>
                        </div>

                        {{-- Thematic area --}}
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-secondary small">Area tematica</div>
                            <div class="fw-semibold">
                                {{ $project->thematicArea->name ?? 'No definida' }}
                            </div>
                        </div>

                        {{-- Investigation line --}}
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-secondary small">Linea de investigacion</div>
                            <div class="fw-semibold">
                                {{ $project->thematicArea->investigationLine->name ?? 'No definida' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Versions table --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de versiones</h3>

                    {{-- Visible records count --}}
                    <div class="card-actions">
                        <span class="badge bg-primary-lt">{{ $versions->total() }} visibles</span>
                    </div>
                </div>

                {{-- Table wrapper --}}
                <div class="table-responsive">
                    <table class="table card-table table-vcenter align-middle">
                        <thead>
                            <tr>
                                <th class="w-1">Version</th>
                                <th>Fecha</th>
                                <th>Registrada por</th>
                                <th>Estado</th>
                                <th>Resumen</th>
                                <th class="w-1 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            {{-- Loop through versions --}}
                            @forelse ($versions as $version)
                                <tr>
                                    {{-- Version number --}}
                                    <td>
                                        <span class="badge bg-primary-lt">
                                            V{{ $version->history_number }}
                                        </span>
                                    </td>

                                    {{-- Creation date --}}
                                    <td>
                                        {{ optional($version->created_at)->format('d/m/Y H:i') }}
                                    </td>

                                    {{-- Author --}}
                                    <td>{{ $version->history_author }}</td>

                                    {{-- Status --}}
                                    <td>{{ $version->history_status }}</td>

                                    {{-- Summary --}}
                                    <td>
                                        <div class="text-secondary small">
                                            {{ $version->history_contents_count }} contenidos 
                                            y {{ $version->history_frameworks_count }} marcos.
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td>
                                        <div class="btn-list flex-nowrap justify-content-center">
                                            {{-- View version details --}}
                                            <a href="{{ route('projects.versions.show', [$project, $version]) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Ver detalle">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="2" />
                                                    <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            {{-- Empty state --}}
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty py-5">
                                            <p class="empty-title">
                                                Este proyecto todavia no tiene historial de versiones.
                                            </p>
                                            <p class="empty-subtitle text-secondary">
                                                La primera version se crea al registrar el proyecto y las siguientes al editarlo.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($versions->hasPages())
                    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">

                        {{-- Pagination info --}}
                        <div class="text-muted small">
                            Mostrando {{ $versions->firstItem() ?? 0 }}-
                            {{ $versions->lastItem() ?? 0 }} 
                            de {{ $versions->total() }} registros
                        </div>

                        {{-- Pagination links --}}
                        <nav aria-label="Paginacion del historial de versiones">
                            {{ $versions->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
