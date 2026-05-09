@extends('tablar::page')
@section('title', 'Detalle de ventana de calendario')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">{{ $processOptions[$window->process_key] ?? $window->process_key }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('academic-process-windows.index') }}" class="btn btn-outline-secondary">Volver</a>
                    <a href="{{ route('academic-process-windows.edit', $window) }}" class="btn btn-primary">Editar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                @php
                    $badgeClass = match($window->calculated_status_key) {
                        'active' => 'bg-success-lt text-success',
                        'scheduled' => 'bg-yellow-lt text-yellow',
                        'closed' => 'bg-secondary-lt text-secondary',
                        default => 'bg-muted-lt text-muted',
                    };
                @endphp

                <dl class="row mb-0">
                    <dt class="col-sm-3">Periodo</dt>
                    <dd class="col-sm-9">{{ $window->academicPeriod?->name }}</dd>

                    <dt class="col-sm-3">Proceso</dt>
                    <dd class="col-sm-9">{{ $processOptions[$window->process_key] ?? $window->process_key }}</dd>

                    <dt class="col-sm-3">Apertura</dt>
                    <dd class="col-sm-9">{{ $window->start_at?->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-3">Cierre</dt>
                    <dd class="col-sm-9">{{ $window->end_at?->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-3">Estado actual</dt>
                    <dd class="col-sm-9">
                        <span class="badge {{ $badgeClass }}">{{ $window->calculated_status }}</span>
                    </dd>

                    <dt class="col-sm-3">Observaciones</dt>
                    <dd class="col-sm-9">{{ $window->notes ?: 'Sin observaciones' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
