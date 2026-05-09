<?php

namespace App\Http\Requests;

use App\Models\AcademicPeriod;
use App\Models\ResearchStaff\ResearchStaffAcademicPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => trim((string) $this->input('code')),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        $academicPeriodId = $this->route('academic_period')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_periods', 'code')->ignore($academicPeriodId)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', Rule::in(array_keys(AcademicPeriod::statusOptions()))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startDate = $this->date('start_date');
            $endDate = $this->date('end_date');

            if (! $startDate || ! $endDate || $validator->errors()->hasAny(['start_date', 'end_date'])) {
                return;
            }

            if ($this->input('status') === AcademicPeriod::STATUS_ACTIVE) {
                $today = CarbonImmutable::today();

                if (! $today->betweenIncluded(
                    $startDate->copy()->startOfDay(),
                    $endDate->copy()->endOfDay()
                )) {
                    $validator->errors()->add(
                        'status',
                        'Solo puedes activar el periodo cuando la fecha actual este entre el inicio y el cierre configurados.'
                    );
                }
            }

            $currentPeriod = $this->route('academic_period');
            $currentPeriodId = $currentPeriod?->id;
            $startDateValue = $startDate->toDateString();
            $endDateValue = $endDate->toDateString();

            if (ResearchStaffAcademicPeriod::overlapsRange($startDateValue, $endDateValue, $currentPeriodId)) {
                $validator->errors()->add(
                    'start_date',
                    'Las fechas del periodo no pueden solaparse con un periodo academico existente.'
                );
            }

            if (! $currentPeriodId) {
                $lastRegisteredPeriod = ResearchStaffAcademicPeriod::lastInSequence();

                if ($lastRegisteredPeriod?->end_date && $startDate->lte($lastRegisteredPeriod->end_date)) {
                    $validator->errors()->add(
                        'start_date',
                        'No se pueden registrar periodos con fechas anteriores a la secuencia academica ya creada.'
                    );
                }

                return;
            }

            ['previous' => $previousPeriod, 'next' => $nextPeriod] = ResearchStaffAcademicPeriod::adjacentPeriodsFor($currentPeriod);

            if ($previousPeriod?->end_date && $startDate->lte($previousPeriod->end_date)) {
                $validator->errors()->add(
                    'start_date',
                    'La fecha de inicio debe ser posterior al periodo academico anterior.'
                );
            }

            if ($nextPeriod?->start_date && $endDate->gte($nextPeriod->start_date)) {
                $validator->errors()->add(
                    'end_date',
                    'Al editar, el periodo debe mantenerse entre el periodo anterior y el siguiente.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El codigo del periodo es obligatorio.',
            'code.unique' => 'Ya existe un periodo con ese codigo.',
            'name.required' => 'El nombre del periodo es obligatorio.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'end_date.required' => 'La fecha de cierre es obligatoria.',
            'end_date.after' => 'La fecha de cierre debe ser posterior a la fecha de inicio.',
            'status.required' => 'Debes seleccionar un estado.',
        ];
    }
}
