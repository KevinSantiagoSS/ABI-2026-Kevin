@extends('tablar::page')

@section('title', 'Mi carga asignada')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Proyectos</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mi carga</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Consulta personal de carga</h2>
                    <p class="text-muted mb-0">Revisa tu asignacion vigente, tu avance de formulacion y las alertas del banco de ideas de tu programa.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    @include('projects.partials.teacher-load-summary', ['loadSummary' => $loadSummary])

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Historico reciente</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Periodo</th>
                                        <th>Programa</th>
                                        <th>Horas</th>
                                        <th>Ideas registradas</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($loadSummary['history'] ?? collect()) as $assignment)
                                        <tr>
                                            <td>{{ $assignment->academicPeriod?->name ?? 'Sin periodo' }}</td>
                                            <td>{{ $assignment->program?->name ?? 'Sin programa' }}</td>
                                            <td>{{ $assignment->assigned_hours }}</td>
                                            <td>{{ $assignment->registered_ideas }}</td>
                                            <td>
                                                @if ($assignment->missing_ideas > 0)
                                                    <span class="text-warning">Faltan {{ $assignment->missing_ideas }}</span>
                                                @elseif ($assignment->goal_reached)
                                                    <span class="text-success">Cumplido (+{{ $assignment->excess_ideas }})</span>
                                                @else
                                                    <span class="text-secondary">Sin meta registrada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-secondary">No hay asignaciones registradas para mostrar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    @include('projects.partials.idea-balance-alerts', ['ideaBalance' => $ideaBalance])
                </div>
            </div>
        </div>
    </div>
@endsection
