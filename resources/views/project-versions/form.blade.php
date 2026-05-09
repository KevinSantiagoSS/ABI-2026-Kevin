{{-- 
    View path: resources/views/project-versions/form.blade.php
    Purpose: Displays a read-only summary of a project version including general information, contents, frameworks, and participants.
    This partial is used to visualize stored snapshot data of a project version.
    Dynamic variables used: $snapshot (main data source), $project (fallback for title), $contents, $frameworks, $participants.
    Included partials or components: None.
    All markup below follows Tablar styling conventions for visual consistency.
--}}

@php
    // Initialize snapshot data with default values if not provided
    $snapshot = $snapshot ?? [];

    // Extract specific sections from snapshot
    $contents = $snapshot['contents'] ?? [];
    $frameworks = $snapshot['frameworks'] ?? [];

    // Initialize participants structure with default empty arrays
    $participants = $snapshot['participants'] ?? ['professors' => [], 'students' => []];
@endphp

<div class="row g-3">
    <div class="col-12 col-lg-8">

        {{-- Card: General version summary --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Resumen de la version</h3>
            </div>
            <div class="card-body">
                <dl class="row g-3 mb-0">

                    {{-- Project title --}}
                    <dt class="col-sm-4">Titulo</dt>
                    <dd class="col-sm-8">{{ $snapshot['title'] ?? $project->title }}</dd>

                    {{-- Project status --}}
                    <dt class="col-sm-4">Estado</dt>
                    <dd class="col-sm-8">{{ data_get($snapshot, 'project_status.name', 'Sin estado') }}</dd>

                    {{-- Thematic area --}}
                    <dt class="col-sm-4">Area tematica</dt>
                    <dd class="col-sm-8">{{ data_get($snapshot, 'thematic_area.name', 'No definida') }}</dd>

                    {{-- Investigation line --}}
                    <dt class="col-sm-4">Linea de investigacion</dt>
                    <dd class="col-sm-8">{{ data_get($snapshot, 'investigation_line.name', 'No definida') }}</dd>

                    {{-- Evaluation criteria (only if exists) --}}
                    @if (!empty($snapshot['evaluation_criteria']))
                        <dt class="col-sm-4">Criterios de evaluacion</dt>
                        <dd class="col-sm-8 text-prewrap">{{ $snapshot['evaluation_criteria'] }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Card: Registered contents --}}
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Contenidos registrados</h3>

                {{-- Display total number of contents --}}
                <span class="badge bg-primary">{{ count($contents) }}</span>
            </div>
            <div class="card-body">

                {{-- Check if contents exist --}}
                @if (count($contents))
                    <dl class="row g-3 mb-0">
                        @foreach ($contents as $label => $value)
                            {{-- Content label --}}
                            <dt class="col-sm-4">{{ $label }}</dt>

                            {{-- Content value --}}
                            <dd class="col-sm-8 text-prewrap">{{ $value }}</dd>
                        @endforeach
                    </dl>
                @else
                    {{-- Empty state --}}
                    <p class="text-secondary mb-0">Esta version no tiene contenidos registrados.</p>
                @endif
            </div>
        </div>

        {{-- Card: Applied frameworks --}}
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Marcos aplicados</h3>

                {{-- Display total number of frameworks --}}
                <span class="badge bg-primary">{{ count($frameworks) }}</span>
            </div>
            <div class="card-body">

                {{-- Check if frameworks exist --}}
                @if (count($frameworks))
                    <div class="row g-3">
                        @foreach ($frameworks as $framework)
                            <div class="col-12">
                                {{-- Framework name --}}
                                <div class="fw-semibold">
                                    {{ data_get($framework, 'framework.name', 'Marco') }}
                                </div>

                                {{-- Framework content/description --}}
                                <div class="text-secondary small">
                                    {{ $framework['name'] ?? 'Sin contenido de marco' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Empty state --}}
                    <p class="text-secondary mb-0">Esta version no registra marcos aplicados.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">

        {{-- Card: Professors --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Profesores</h3>

                {{-- Display number of professors --}}
                <span class="badge bg-primary">{{ count($participants['professors'] ?? []) }}</span>
            </div>
            <div class="card-body">

                {{-- Loop through professors list --}}
                @forelse (($participants['professors'] ?? []) as $professor)
                    <div class="mb-3">
                        {{-- Professor name --}}
                        <div class="fw-semibold">{{ $professor['name'] ?? 'Profesor' }}</div>

                        {{-- Professor email --}}
                        <div class="text-secondary small">
                            {{ $professor['email'] ?? 'Correo no disponible' }}
                        </div>
                    </div>
                @empty
                    {{-- Empty state --}}
                    <p class="text-secondary mb-0">Sin profesores asociados.</p>
                @endforelse
            </div>
        </div>

        {{-- Card: Students --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Estudiantes</h3>

                {{-- Display number of students --}}
                <span class="badge bg-primary">{{ count($participants['students'] ?? []) }}</span>
            </div>
            <div class="card-body">

                {{-- Loop through students list --}}
                @forelse (($participants['students'] ?? []) as $student)
                    <div class="mb-3">
                        {{-- Student name --}}
                        <div class="fw-semibold">{{ $student['name'] ?? 'Estudiante' }}</div>

                        {{-- Student document ID --}}
                        <div class="text-secondary small">
                            Documento: {{ $student['card_id'] ?? 'No disponible' }}
                        </div>
                    </div>
                @empty
                    {{-- Empty state --}}
                    <p class="text-secondary mb-0">Sin estudiantes asociados.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
