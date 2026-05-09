@php
    $ideaBalance = $ideaBalance ?? null;
@endphp

@if ($ideaBalance)
    @php
        $recommendedAlerts = collect($ideaBalance['alerts'] ?? [])->where('section', 'recommended')->values();
        $lowAlerts = collect($ideaBalance['alerts'] ?? [])->where('section', 'low')->values();
        $avoidAlerts = collect($ideaBalance['alerts'] ?? [])->where('section', 'avoid')->values();
    @endphp
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Recomendaciones para nuevas ideas</h3>
        </div>
        <div class="card-body">
            @if (! ($ideaBalance['has_data'] ?? false))
                <div class="alert alert-warning mb-0">
                    {{ $ideaBalance['empty_message'] ?? 'No fue posible calcular recomendaciones para este momento.' }}
                </div>
            @else
                <div class="text-secondary mb-3">
                    Sugerencias para proponer ideas en <strong>{{ $ideaBalance['program']?->name }}</strong> durante el periodo
                    <strong>{{ $ideaBalance['active_period']?->name }}</strong>.
                </div>

                @if ($recommendedAlerts->isNotEmpty())
                    <div class="mb-3">
                        <h4 class="mb-2 text-success">Lineas o areas recomendadas</h4>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($recommendedAlerts as $alert)
                                <div class="alert alert-success mb-0">
                                    <strong>{{ $alert['title'] }}:</strong> {{ $alert['message'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($lowAlerts->isNotEmpty())
                    <div class="mb-3">
                        <h4 class="mb-2 text-warning">Categorias con poca disponibilidad</h4>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($lowAlerts as $alert)
                                <div class="alert alert-warning mb-0">
                                    <strong>{{ $alert['title'] }}:</strong> {{ $alert['message'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($avoidAlerts->isNotEmpty())
                    <div>
                        <h4 class="mb-2 text-danger">Evitar repetir propuestas</h4>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($avoidAlerts as $alert)
                                <div class="alert alert-danger mb-0">
                                    <strong>{{ $alert['title'] }}:</strong> {{ $alert['message'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endif
