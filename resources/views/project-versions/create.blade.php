{{-- 
    View path: resources/views/project-versions/create.blade.php
    Purpose: Displays an informational view about the automatic generation of project versions.
    This view informs users that versions are not created manually but automatically by the system.
    Dynamic variables used: $project (used to display project ID and title).
    Included partials or components: None.
    All markup below follows Tablar styling conventions for visual consistency.
--}}
@extends('tablar::page')

@section('title', 'Versiones automaticas')

@section('content')
    {{-- Page header section with breadcrumb navigation --}}
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    {{-- Breadcrumb navigation to show user location --}}
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
                                {{-- Link to the current project details --}}
                                <a href="{{ route('projects.show', $project) }}">Proyecto #{{ $project->id }}</a>
                            </li>
                            {{-- Current page indicator --}}
                            <li class="breadcrumb-item active" aria-current="page">
                                Generacion de versiones
                            </li>
                        </ol>
                    </nav>

                    {{-- Page title --}}
                    <h2 class="page-title">Versiones automaticas del proyecto</h2>

                    {{-- Description explaining the behavior of the module --}}
                    <p class="text-muted mb-0">
                        Las versiones de este modulo no se crean manualmente.
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
                    {{-- Explanation about automatic version creation --}}
                    <p class="mb-3">
                        Cada vez que el proyecto 
                        <strong>{{ $project->title }}</strong> 
                        se crea o se actualiza, el sistema genera una nueva version de forma automatica y la agrega al historial.
                    </p>

                    {{-- Action buttons --}}
                    <div class="btn-list">
                        {{-- Button to view version history --}}
                        <a href="{{ route('projects.versions.index', $project) }}" class="btn btn-primary">
                            Ver historial
                        </a>

                        {{-- Button to return to project detail --}}
                        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
                            Volver al proyecto
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
