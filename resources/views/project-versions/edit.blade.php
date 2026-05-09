{{-- 
    View path: resources/views/project-versions/edit.blade.php
    Purpose: Displays a read-only informational view for a specific project version.
    This view prevents manual editing and explains that versions are preserved as historical records.
    Dynamic variables used: $project (used for navigation and project reference), $version (used to display version ID).
    Included partials or components: None.
    All markup below follows Tablar styling conventions for visual consistency.
--}}
@extends('tablar::page')

@section('title', 'Version de solo lectura')

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
                                {{-- Link to home page --}}
                                <a href="{{ route('home') }}">Inicio</a>
                            </li>
                            <li class="breadcrumb-item">
                                {{-- Link to projects list --}}
                                <a href="{{ route('projects.index') }}">Proyectos</a>
                            </li>
                            <li class="breadcrumb-item">
                                {{-- Link to project details --}}
                                <a href="{{ route('projects.show', $project) }}">Proyecto #{{ $project->id }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                {{-- Link to version history --}}
                                <a href="{{ route('projects.versions.index', $project) }}">Historial</a>
                            </li>
                            {{-- Current page indicator --}}
                            <li class="breadcrumb-item active" aria-current="page">
                                Solo lectura
                            </li>
                        </ol>
                    </nav>

                    {{-- Page title --}}
                    <h2 class="page-title">Las versiones no se editan manualmente</h2>

                    {{-- Description showing version information --}}
                    <p class="text-muted mb-0">
                        La version #{{ $version->id }} permanece como evidencia historica del proyecto.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content section --}}
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    {{-- Explanation about version immutability --}}
                    <p class="mb-3">
                        Para generar una nueva version debes editar el proyecto. 
                        El sistema conservara esta version y creara una nueva entrada en el historial.
                    </p>

                    {{-- Action buttons --}}
                    <div class="btn-list">
                        {{-- Button to view version details --}}
                        <a href="{{ route('projects.versions.show', [$project, $version]) }}" class="btn btn-primary">
                            Ver detalle de la version
                        </a>

                        {{-- Button to edit the project (to generate a new version) --}}
                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary">
                            Editar proyecto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
