<?php

namespace App\Http\Requests;

use App\Services\Projections\ProjectionPeriodService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoadProjectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $currentProjection = $this->route('load_projection');
        $targetPeriodId = $currentProjection?->academic_period_id
            ?? app(ProjectionPeriodService::class)->targetPeriod()?->id;

        $this->merge([
            'academic_period_id' => $targetPeriodId,
            'projected_pg1_students' => $this->filled('projected_pg1_students')
                ? max((int) $this->input('projected_pg1_students'), 0)
                : null,
            'observations' => ($observations = trim((string) $this->input('observations'))) !== '' ? $observations : null,
        ]);
    }

    public function rules(): array
    {
        $projectionId = $this->route('load_projection')?->id;

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
                Rule::unique('load_projections', 'program_id')
                    ->where(fn ($query) => $query->where('academic_period_id', $this->input('academic_period_id')))
                    ->ignore($projectionId),
            ],
            'projected_pg1_students' => ['required', 'integer', 'min:0', 'max:9999'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->input('academic_period_id')) {
                $validator->errors()->add(
                    'academic_period_id',
                    'Debes configurar un periodo academico activo y el siguiente periodo antes de registrar proyecciones.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'program_id.required' => 'Debes seleccionar un programa academico.',
            'program_id.unique' => 'Ya existe una proyeccion de carga para este programa en el periodo objetivo.',
            'projected_pg1_students.required' => 'Debes registrar la proyeccion de estudiantes PG1.',
            'projected_pg1_students.integer' => 'La proyeccion de estudiantes PG1 debe ser numerica.',
            'projected_pg1_students.min' => 'La proyeccion de estudiantes PG1 no puede ser negativa.',
        ];
    }
}
