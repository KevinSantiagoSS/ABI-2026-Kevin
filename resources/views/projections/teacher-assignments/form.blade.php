@php
    $isEdit = isset($teacherAssignment) && $teacherAssignment->exists;
    $selectedProgramId = (int) old('program_id', $teacherAssignment->program_id);
    $selectedProfessorId = (int) old('professor_id', $teacherAssignment->professor_id);
@endphp

<div class="row g-3">
    <div class="col-12 col-lg-6">
        <label class="form-label">Periodo objetivo</label>
        <input type="text" class="form-control" value="{{ $targetPeriod?->name }}" readonly>
    </div>
    <div class="col-12 col-lg-6">
        <label class="form-label">Continuidad PG2</label>
        <input type="text" class="form-control" value="Se sugieren docentes activos que ya acompanian los PG1 vigentes." readonly>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
        <label for="program_id" class="form-label required">Programa academico</label>
        @if($lockIdentityFields)
            <input type="hidden" name="program_id" value="{{ $selectedProgramId }}">
            <input type="text" class="form-control" value="{{ $teacherAssignment->program?->name }}" readonly>
        @else
            <select id="program_id" name="program_id" class="form-select {{ $errors->has('program_id') ? 'is-invalid' : '' }}" required>
                <option value="">Selecciona un programa...</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ $selectedProgramId === (int) $program->id ? 'selected' : '' }}>
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
        <label for="professor_id" class="form-label required">Docente</label>
        @if($lockIdentityFields)
            <input type="hidden" name="professor_id" value="{{ $selectedProfessorId }}">
            <input
                type="text"
                class="form-control"
                value="{{ trim(($teacherAssignment->professor?->name ?? '') . ' ' . ($teacherAssignment->professor?->last_name ?? '')) }}"
                readonly
            >
        @else
            <select id="professor_id" name="professor_id" class="form-select {{ $errors->has('professor_id') ? 'is-invalid' : '' }}" required disabled></select>
            @error('professor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-hint">La lista se filtra automaticamente por programa y se agrupa por sede/ciudad.</small>
        @endif
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-4">
        <label for="assigned_hours" class="form-label required">Horas asignadas</label>
        <input
            type="number"
            id="assigned_hours"
            name="assigned_hours"
            class="form-control {{ $errors->has('assigned_hours') ? 'is-invalid' : '' }}"
            min="1"
            value="{{ old('assigned_hours', $teacherAssignment->assigned_hours) }}"
            required
        >
        @error('assigned_hours')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 col-lg-8">
        <label for="observations" class="form-label">Observaciones</label>
        <textarea
            id="observations"
            name="observations"
            rows="3"
            class="form-control {{ $errors->has('observations') ? 'is-invalid' : '' }}"
        >{{ old('observations', $teacherAssignment->observations) }}</textarea>
        @error('observations')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@unless($lockIdentityFields)
    <div class="card bg-light mt-4" id="continuity-card">
        <div class="card-body">
            <h3 class="card-title mb-2">Sugerencias por continuidad PG2</h3>
            <div id="continuity-content" class="text-muted">Selecciona un programa para ver docentes sugeridos por continuidad.</div>
        </div>
    </div>
@endunless

<div class="alert alert-secondary mt-3 mb-0">
    Regla de seguimiento: 1 hora asignada equivale a 1 idea esperada para el docente.
</div>

<div class="form-footer d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('projections.teacher-assignments.index') }}" class="btn btn-link">Cancelar</a>
    <button type="submit" class="btn btn-primary">
        {{ $isEdit ? 'Actualizar asignacion' : 'Guardar asignacion' }}
    </button>
</div>

@unless($lockIdentityFields)
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const directory = @json($teacherDirectory);
                const programSelect = document.getElementById('program_id');
                const professorSelect = document.getElementById('professor_id');
                const continuityContent = document.getElementById('continuity-content');
                const initialProfessorId = @json((string) $selectedProfessorId);

                if (!programSelect || !professorSelect) {
                    return;
                }

                const renderSuggestions = programData => {
                    continuityContent.innerHTML = '';

                    if (!programData || !programData.suggestions || programData.suggestions.length === 0) {
                        continuityContent.textContent = 'No hay sugerencias de continuidad PG2 para el programa seleccionado.';
                        return;
                    }

                    const list = document.createElement('ul');
                    list.className = 'mb-0';

                    programData.suggestions.forEach(item => {
                        const line = document.createElement('li');
                        line.textContent = `${item.name} (${item.city ?? 'Sin ciudad'}) - ${item.group_count} grupos, ${item.student_count} estudiantes.`;
                        list.appendChild(line);
                    });

                    continuityContent.appendChild(list);
                };

                const populateProfessors = () => {
                    const programId = programSelect.value;
                    const programData = directory[programId];

                    professorSelect.innerHTML = '';

                    if (!programId || !programData || !programData.teachers.length) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Selecciona primero un programa...';
                        professorSelect.appendChild(option);
                        professorSelect.disabled = true;
                        renderSuggestions(programData);
                        return;
                    }

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Selecciona un docente...';
                    professorSelect.appendChild(placeholder);

                    const groupsByCity = {};

                    programData.teachers.forEach(teacher => {
                        const city = teacher.city || 'Sin ciudad';
                        groupsByCity[city] = groupsByCity[city] || [];
                        groupsByCity[city].push(teacher);
                    });

                    Object.keys(groupsByCity).sort().forEach(city => {
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = city;

                        groupsByCity[city].forEach(teacher => {
                            const option = document.createElement('option');
                            option.value = teacher.id;
                            option.textContent = teacher.suggested
                                ? `${teacher.label} (continuidad PG2: ${teacher.continuity_groups} grupos)`
                                : teacher.label;
                            optgroup.appendChild(option);
                        });

                        professorSelect.appendChild(optgroup);
                    });

                    professorSelect.disabled = false;
                    professorSelect.value = initialProfessorId;
                    renderSuggestions(programData);
                };

                programSelect.addEventListener('change', () => {
                    professorSelect.value = '';
                    populateProfessors();
                });

                populateProfessors();
            });
        </script>
    @endpush
@endunless
