@php
    $isEdit = isset($academicPeriod) && $academicPeriod->exists;
    $dateConstraints = $dateConstraints ?? [];
    $startMin = $dateConstraints['start_min'] ?? null;
    $startMax = $dateConstraints['start_max'] ?? null;
    $endMin = $dateConstraints['end_min'] ?? null;
    $endMax = $dateConstraints['end_max'] ?? null;
    $today = now()->toDateString();
@endphp

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="code" class="form-label required">Codigo</label>
        <input
            type="text"
            id="code"
            name="code"
            class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
            value="{{ old('code', $academicPeriod->code ?? '') }}"
            placeholder="Ej: 2026-1"
            required
        >
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-8">
        <label for="name" class="form-label required">Nombre del periodo</label>
        <input
            type="text"
            id="name"
            name="name"
            class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
            value="{{ old('name', $academicPeriod->name ?? '') }}"
            placeholder="Periodo academico 2026-1"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-md-4">
        <label for="start_date" class="form-label required">Fecha de inicio</label>
        <input
            type="date"
            id="start_date"
            name="start_date"
            class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
            value="{{ old('start_date', optional($academicPeriod->start_date)->format('Y-m-d')) }}"
            min="{{ $startMin }}"
            max="{{ $startMax }}"
            data-static-min="{{ $startMin }}"
            data-static-max="{{ $startMax }}"
            required
        >
        @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="end_date" class="form-label required">Fecha de cierre</label>
        <input
            type="date"
            id="end_date"
            name="end_date"
            class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
            value="{{ old('end_date', optional($academicPeriod->end_date)->format('Y-m-d')) }}"
            min="{{ $endMin }}"
            max="{{ $endMax }}"
            data-static-min="{{ $endMin }}"
            data-static-max="{{ $endMax }}"
            required
        >
        @error('end_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="status" class="form-label required">Estado</label>
        <select
            id="status"
            name="status"
            class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}"
            data-today="{{ $today }}"
            required
        >
            @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}" {{ old('status', $academicPeriod->status ?? 'draft') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <small id="status-help" class="form-hint text-muted"></small>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<hr class="my-4">

<div class="form-footer d-flex flex-column flex-md-row justify-content-end gap-2">
    <a href="{{ route('academic-periods.index') }}" class="btn btn-link">Cancelar</a>
    <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Actualizar periodo' : 'Crear periodo' }}</button>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const statusSelect = document.getElementById('status');
    const statusHelp = document.getElementById('status-help');

    if (!startInput || !endInput || !statusSelect) {
        return;
    }

    const addDays = (value, days) => {
        if (!value) {
            return '';
        }

        const date = new Date(value + 'T00:00:00');
        date.setDate(date.getDate() + days);

        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${date.getFullYear()}-${month}-${day}`;
    };

    const laterDate = (first, second) => {
        return [first, second].filter(Boolean).sort().slice(-1)[0] || '';
    };

    const earlierDate = (first, second) => {
        return [first, second].filter(Boolean).sort()[0] || '';
    };

    const staticStartMin = startInput.dataset.staticMin || '';
    const staticStartMax = startInput.dataset.staticMax || '';
    const staticEndMin = endInput.dataset.staticMin || '';
    const staticEndMax = endInput.dataset.staticMax || '';
    const today = statusSelect.dataset.today || '';
    const activeOption = statusSelect.querySelector('option[value="active"]');
    const draftOption = statusSelect.querySelector('option[value="draft"]');

    const periodIncludesToday = () => {
        if (!today || !startInput.value || !endInput.value) {
            return false;
        }

        return startInput.value <= today && today <= endInput.value;
    };

    const syncDateConstraints = () => {
        const nextEndMin = laterDate(staticEndMin, addDays(startInput.value, 1));
        const nextStartMax = earlierDate(staticStartMax, endInput.value ? addDays(endInput.value, -1) : '');

        if (staticStartMin) {
            startInput.min = staticStartMin;
        }

        if (nextStartMax) {
            startInput.max = nextStartMax;
        } else if (staticStartMax) {
            startInput.max = staticStartMax;
        } else {
            startInput.removeAttribute('max');
        }

        if (nextEndMin) {
            endInput.min = nextEndMin;
        } else if (staticEndMin) {
            endInput.min = staticEndMin;
        } else {
            endInput.removeAttribute('min');
        }

        if (staticEndMax) {
            endInput.max = staticEndMax;
        }
    };

    const syncStatusAvailability = () => {
        if (!activeOption) {
            return;
        }

        const canActivate = periodIncludesToday();

        activeOption.disabled = !canActivate;

        if (statusHelp) {
            statusHelp.textContent = canActivate
                ? ''
                : 'El estado Activo solo se puede seleccionar cuando la fecha actual este dentro del rango del periodo.';
        }

        if (!canActivate && statusSelect.value === 'active') {
            statusSelect.value = draftOption ? 'draft' : statusSelect.value;
        }
    };

    const syncFormState = () => {
        syncDateConstraints();
        syncStatusAvailability();
    };

    startInput.addEventListener('change', syncFormState);
    endInput.addEventListener('change', syncFormState);

    syncFormState();
});
</script>
@endpush
