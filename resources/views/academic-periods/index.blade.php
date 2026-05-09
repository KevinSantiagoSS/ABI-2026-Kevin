@extends('tablar::page')
@section('title', 'Periodos académicos')
@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Gestión de periodos académicos</h2>
                <p class="text-muted mb-0">Administra los semestres institucionales y controla cuál se encuentra vigente.</p>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('academic-process-windows.index') }}" class="btn btn-outline-secondary">Calendario académico</a>
                    <a href="{{ route('academic-periods.create') }}" class="btn btn-primary">Nuevo periodo</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(config('tablar.display_alert'))
            @include('tablar::common.alert')
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('academic-periods.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Código o nombre">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ (string) $status === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Registros</label>
                        <select name="per_page" class="form-select">
                            @foreach([10, 25, 50] as $size)
                                <option value="{{ $size }}" {{ (int) $perPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-outline-primary">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Periodo</th>
                            <th>Inicio</th>
                            <th>Cierre</th>
                            <th>Estado</th>
                            <th>Ventanas</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($academicPeriods as $period)
                            @php
                                $badge = $period->status === 'active'
                                    ? 'bg-green-lt'
                                    : ($period->status === 'closed' ? 'bg-red-lt' : 'bg-yellow-lt');
                                $canActivateToday = $period->canBeActivatedOn(now());
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $period->code }}</div>
                                    <div class="text-muted">{{ $period->name }}</div>
                                </td>
                                <td>{{ $period->start_date?->format('d/m/Y') }}</td>
                                <td>{{ $period->end_date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $badge }}">{{ $statusOptions[$period->status] ?? ucfirst($period->status) }}</span>
                                    @if($period->is_active)
                                        <span class="badge bg-blue-lt">Vigente</span>
                                    @endif
                                </td>
                                <td>{{ $period->process_windows_count }}</td>
                                <td>
                                    <div class="btn-list justify-content-center flex-nowrap">
                                        <a href="{{ route('academic-periods.show', $period) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                        <a href="{{ route('academic-periods.edit', $period) }}" class="btn btn-sm btn-outline-success">Editar</a>
                                        <a href="{{ route('academic-process-windows.create', ['academic_period_id' => $period->id]) }}" class="btn btn-sm btn-outline-secondary">Calendario</a>

                                        @if(! $period->is_active)
                                            @if($canActivateToday)
                                                <form action="{{ route('academic-periods.activate', $period) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-info">Activar</button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No se puede activar porque hoy está fuera de las fechas del periodo.">Activar</button>
                                            @endif
                                        @endif

                                        @if($period->status !== 'closed')
                                            <form action="{{ route('academic-periods.close', $period) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Cerrar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty">
                                        <p class="empty-title">No hay periodos académicos registrados</p>
                                        <p class="empty-subtitle text-muted">Crea el primer periodo para habilitar el calendario institucional.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($academicPeriods->hasPages())
                <div class="card-footer">{{ $academicPeriods->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
