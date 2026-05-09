<?php

namespace App\Http\Requests;

use App\Models\ResearchStaff\ResearchStaffProfessor;
use App\Services\Projections\ProjectionPeriodService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $currentAssignment = $this->route('teacher_assignment');
        $targetPeriodId = $currentAssignment?->academic_period_id
            ?? app(ProjectionPeriodService::class)->targetPeriod()?->id;

        $this->merge([
            'academic_period_id' => $targetPeriodId,
            'assigned_hours' => $this->filled('assigned_hours')
                ? max((int) $this->input('assigned_hours'), 0)
                : null,
            'observations' => ($observations = trim((string) $this->input('observations'))) !== '' ? $observations : null,
        ]);
    }

    public function rules(): array
    {
        $assignmentId = $this->route('teacher_assignment')?->id;

        return [
            'academic_period_id' => [
                'required',
                'integer',
                Rule::exists('academic_periods', 'id')->whereNull('deleted_at'),
            ],
            'program_id' => [
                'required',
                'integer',
                Rule::exists('programs', 'id')->whereNull('deleted_at'),
            ],
            'professor_id' => [
                'required',
                'integer',
                Rule::exists('professors', 'id')->whereNull('deleted_at'),
                Rule::unique('teacher_assignments', 'professor_id')
                    ->where(fn ($query) => $query
                        ->where('academic_period_id', $this->input('academic_period_id'))
                        ->where('program_id', $this->input('program_id')))
                    ->ignore($assignmentId),
            ],
            'assigned_hours' => ['required', 'integer', 'min:1', 'max:999'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->input('academic_period_id')) {
                $validator->errors()->add(
                    'academic_period_id',
                    'Debes configurar un periodo academico activo y el siguiente periodo antes de registrar asignaciones.'
                );
            }

            if (! $this->filled('program_id') || ! $this->filled('professor_id')) {
                return;
            }

            $professor = ResearchStaffProfessor::query()
                ->with(['user', 'cityProgram'])
                ->find($this->input('professor_id'));

            if (! $professor) {
                return;
            }

            if ((int) ($professor->cityProgram?->program_id ?? 0) !== (int) $this->input('program_id')) {
                $validator->errors()->add(
                    'professor_id',
                    'El docente seleccionado no pertenece al programa academico elegido.'
                );
            }

            if (! ($professor->user?->state ?? false) || $professor->trashed()) {
                $validator->errors()->add(
                    'professor_id',
                    'Solo puedes asignar docentes activos.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'program_id.required' => 'Debes seleccionar un programa academico.',
            'professor_id.required' => 'Debes seleccionar un docente.',
            'professor_id.unique' => 'Ya existe una asignacion para este docente, programa y periodo.',
            'assigned_hours.required' => 'Debes registrar las horas asignadas.',
            'assigned_hours.integer' => 'Las horas asignadas deben ser numericas.',
            'assigned_hours.min' => 'Las horas asignadas deben ser mayores a cero.',
        ];
    }
}
