@php
    $loadSummary = $loadSummary ?? null;
    $showLink = $showLink ?? false;
@endphp

@if ($loadSummary)
    @php
        $activeAssignment = $loadSummary['active_assignment'] ?? null;
        $activePeriod = $loadSummary['active_period'] ?? null;
        $program = $loadSummary['program'] ?? null;
        $city = $loadSummary['city'] ?? null;
        $hasActiveAssignment = (bool) ($loadSummary['has_active_assignment'] ?? false);
        $missingIdeas = (int) data_get($activeAssignment, 'missing_ideas', 0);
        $excessIdeas = (int) data_get($activeAssignment, 'excess_ideas', 0);
        $registeredIdeas = (int) data_get($activeAssignment, 'registered_ideas', 0);
    @endphp

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Mi carga de formulacion</h3>
            @if ($showLink)
                <div class="card-actions">
                    <a href="{{ route('projects.my-load') }}" class="btn btn-outline-primary btn-sm">Ver detalle</a>
                </div>
            @endif
        </div>
        <div class="card-body">
            <div class="text-secondary mb-3">
                <div><strong>Periodo activo:</strong> {{ $activePeriod?->name ?? 'No configurado' }}</div>
                <div><strong>Programa:</strong> {{ $program?->name ?? 'No disponible' }}@if($city?->name) - {{ $city->name }}@endif</div>
                <div><strong>Origen:</strong> {{ data_get($activeAssignment, 'source_context', 'La carga la registra el personal de investigaciones.') }}</div>
            </div>

            @if ($hasActiveAssignment)
                <div class="row g-3">
                    <div class="col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Horas asignadas</div>
                            <div class="h2 mb-0">{{ data_get($activeAssignment, 'assigned_hours', 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Ideas esperadas</div>
                            <div class="h2 mb-0">{{ data_get($activeAssignment, 'expected_ideas', 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Ideas registradas</div>
                            <div class="h2 mb-0">{{ $registeredIdeas }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">{{ $missingIdeas > 0 ? 'Ideas faltantes' : 'Balance actual' }}</div>
                            <div class="h2 mb-0 {{ $missingIdeas > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $missingIdeas > 0 ? $missingIdeas : '+' . $excessIdeas }}
                            </div>
                        </div>
                    </div>
                </div>

                @if (data_get($activeAssignment, 'goal_reached'))
                    <div class="alert alert-success mt-3 mb-0">
                        Ya cumpliste o superaste la meta esperada para este periodo academico.
                    </div>
                @elseif ($missingIdeas > 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        Aun te faltan {{ $missingIdeas }} idea(s) o proyecto(s) por registrar para cumplir la carga esperada.
                    </div>
                @endif

                @if (data_get($activeAssignment, 'observations'))
                    <div class="mt-3">
                        <div class="text-secondary small">Observaciones de la asignacion</div>
                        <div>{{ data_get($activeAssignment, 'observations') }}</div>
                    </div>
                @endif
            @else
                <div class="empty">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-secondary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 9v4" />
                            <path d="M12 17h.01" />
                            <path d="M5.07 19h13.86a2 2 0 0 0 1.75 -2.99l-6.93 -12a2 2 0 0 0 -3.46 0l-6.93 12a2 2 0 0 0 1.75 2.99" />
                        </svg>
                    </div>
                    <p class="empty-title">{{ $loadSummary['empty_message'] ?? 'No hay una asignacion activa registrada.' }}</p>
                    @if (($loadSummary['next_assignment'] ?? null) && data_get($loadSummary['next_assignment'], 'academicPeriod.name'))
                        <p class="empty-subtitle text-secondary">
                            Existe otra asignacion registrada para el periodo {{ data_get($loadSummary['next_assignment'], 'academicPeriod.name') }}.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
