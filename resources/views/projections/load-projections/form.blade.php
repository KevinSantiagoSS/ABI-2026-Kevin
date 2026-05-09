@php
    $isEdit = isset($loadProjection) && $loadProjection->exists;
@endphp

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <label class="form-label">Periodo objetivo</label>
        <input type="text" class="form-control" value="{{ $targetPeriod?->name }}" readonly>
        <small class="form-hint">
            @if($activePeriod)
                Se toma automaticamente el periodo siguiente al actualmente activo: {{ $activePeriod->name }}.
            @else
                Este modulo depende de un periodo academico activo configurado.
            @endif
        </small>
    </div>
    <div class="col-12 col-lg-6">
        <label class="form-label">Regla PG2</label>
        <input type="text" class="form-control" value="{{ $previewMetrics['pg2_source_label'] }}" readonly>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
        <label for="program_id" class="form-label required">Programa academico</label>
        @if($lockProgram)
            <input type="hidden" name="program_id" value="{{ old('program_id', $loadProjection->program_id) }}">
            <input type="text" class="form-control" value="{{ $loadProjection->program?->name }}" readonly>
        @else
            <select id="program_id" name="program_id" class="form-select {{ $errors->has('program_id') ? 'is-invalid' : '' }}" required>
                <option value="">Selecciona un programa...</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ (int) old('program_id', $loadProjection->program_id) === (int) $program->id ? 'selected' : '' }}>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
            @error('program_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-12 col-lg-6">
        <label for="projected_pg1_students" class="form-label required">Estudiantes PG1 proyectados</label>
        <input
            type="number"
            id="projected_pg1_students"
            name="projected_pg1_students"
            class="form-control {{ $errors->has('projected_pg1_students') ? 'is-invalid' : '' }}"
            min="0"
            value="{{ old('projected_pg1_students', $loadProjection->projected_pg1_students) }}"
            required
        >
        @error('projected_pg1_students')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-3">
    <label for="observations" class="form-label">Observaciones</label>
    <textarea
        id="observations"
        name="observations"
        rows="3"
        class="form-control {{ $errors->has('observations') ? 'is-invalid' : '' }}"
    >{{ old('observations', $loadProjection->observations) }}</textarea>
    @error('observations')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr class="my-4">

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label class="form-label">Grupos PG1</label>
        <input type="text" id="metric_pg1_groups" class="form-control" value="{{ $previewMetrics['projected_pg1_groups'] }}" readonly>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Estudiantes PG2</label>
        <input
            type="text"
            id="metric_pg2_students"
            class="form-control"
            value="{{ $previewMetrics['projected_pg2_students'] }}"
            data-pg2-students="{{ $previewMetrics['projected_pg2_students'] }}"
            readonly
        >
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Grupos PG2</label>
        <input type="text" id="metric_pg2_groups" class="form-control" value="{{ $previewMetrics['projected_pg2_groups'] }}" readonly>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Horas semanales PG1</label>
        <input type="text" id="metric_pg1_hours" class="form-control" value="{{ $previewMetrics['pg1_weekly_hours'] }}" readonly>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Horas semanales PG2</label>
        <input type="text" id="metric_pg2_hours" class="form-control" value="{{ $previewMetrics['pg2_weekly_hours'] }}" readonly>
    </div>
    <div class="col-12 col-md-4">
        <label class="form-label">Total horas semanales</label>
        <input type="text" id="metric_total_hours" class="form-control fw-bold" value="{{ $previewMetrics['total_weekly_hours'] }}" readonly>
    </div>
</div>

<div class="alert alert-secondary mt-3 mb-0">
    Los grupos se calculan con maximo 3 estudiantes por grupo y cada grupo equivale a 2 horas semanales.
</div>

<div class="form-footer d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('projections.load-projections.index') }}" class="btn btn-link">Cancelar</a>
    <button type="submit" class="btn btn-primary">
        {{ $isEdit ? 'Actualizar proyeccion' : 'Guardar proyeccion' }}
    </button>
</div>
@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pg1Input = document.getElementById('projected_pg1_students');
            const pg2Input = document.getElementById('metric_pg2_students');
            const pg1Groups = document.getElementById('metric_pg1_groups');
            const pg2Groups = document.getElementById('metric_pg2_groups');
            const pg1Hours = document.getElementById('metric_pg1_hours');
            const pg2Hours = document.getElementById('metric_pg2_hours');
            const totalHours = document.getElementById('metric_total_hours');

            if (!pg1Input || !pg2Input) {
                return;
            }

            const calculateGroups = value => Math.ceil((Number(value) || 0) / 3);

            const renderMetrics = () => {
                const pg1Students = Number(pg1Input.value) || 0;
                const pg2Students = Number(pg2Input.dataset.pg2Students) || 0;
                const pg1GroupCount = calculateGroups(pg1Students);
                const pg2GroupCount = calculateGroups(pg2Students);
                const pg1HourCount = pg1GroupCount * 2;
                const pg2HourCount = pg2GroupCount * 2;

                pg1Groups.value = pg1GroupCount;
                pg2Groups.value = pg2GroupCount;
                pg1Hours.value = pg1HourCount;
                pg2Hours.value = pg2HourCount;
                totalHours.value = pg1HourCount + pg2HourCount;
            };

            pg1Input.addEventListener('input', renderMetrics);
            renderMetrics();
        });
    </script>
@endpush
