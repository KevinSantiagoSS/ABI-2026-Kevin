<?php

namespace App\Services\AcademicCalendar;

use App\Models\AcademicPeriod;
use App\Models\AcademicProcessWindow;
use App\Models\Project;
use App\Models\ProjectStageHistory;
use Carbon\Carbon;

class AcademicCalendarService
{
    public static function currentActivePeriod(): ?AcademicPeriod
    {
        return AcademicPeriod::query()
            ->where('is_active', true)
            ->where('status', AcademicPeriod::STATUS_ACTIVE)
            ->orderByDesc('start_date')
            ->first();
    }

    public static function currentActivePeriodOrFail(): AcademicPeriod
    {
        $period = self::currentActivePeriod();

        if (! $period) {
            abort(403, 'No existe un periodo academico activo en este momento.');
        }

        return $period;
    }

    public static function currentWindowForProcess(
        string $processKey,
        ?Carbon $reference = null,
        ?AcademicPeriod $period = null
    ): ?AcademicProcessWindow {
        $reference ??= now();
        $period ??= self::currentActivePeriod();

        if (! $period) {
            return null;
        }

        return AcademicProcessWindow::query()
            ->where('academic_period_id', $period->id)
            ->where('process_key', $processKey)
            ->where('is_enabled', true)
            ->where('start_at', '<=', $reference)
            ->where('end_at', '>=', $reference)
            ->first();
    }

    public static function configuredWindowForProcess(
        string $processKey,
        ?AcademicPeriod $period = null
    ): ?AcademicProcessWindow {
        $period ??= self::currentActivePeriod();

        if (! $period) {
            return null;
        }

        return AcademicProcessWindow::query()
            ->where('academic_period_id', $period->id)
            ->where('process_key', $processKey)
            ->orderBy('start_at')
            ->first();
    }

    public static function isProcessWindowOpen(string $processKey, ?Carbon $reference = null): bool
    {
        return self::currentWindowForProcess($processKey, $reference) !== null;
    }

    public static function processLabel(string $processKey): string
    {
        return AcademicProcessWindow::processOptions()[$processKey] ?? $processKey;
    }

    public static function processWindowUnavailableMessage(string $processKey, ?Carbon $reference = null): string
    {
        $reference ??= now();
        $period = self::currentActivePeriod();
        $processLabel = mb_strtolower(self::processLabel($processKey));

        if (! $period) {
            return 'No existe un periodo academico activo. No es posible continuar con ' . $processLabel . '.';
        }

        $configuredWindow = self::configuredWindowForProcess($processKey, $period);

        if (! $configuredWindow) {
            return 'La ventana de ' . $processLabel . ' no esta configurada para el periodo ' . $period->name . '.';
        }

        if (! $configuredWindow->is_enabled) {
            return 'La ventana de ' . $processLabel . ' para el periodo ' . $period->name . ' esta deshabilitada.';
        }

        if ($reference->lt($configuredWindow->start_at)) {
            return 'La ventana de ' . $processLabel . ' para el periodo ' . $period->name . ' inicia el ' . $configuredWindow->start_at->format('d/m/Y H:i') . '.';
        }

        if ($reference->gt($configuredWindow->end_at)) {
            return 'La ventana de ' . $processLabel . ' para el periodo ' . $period->name . ' finalizo el ' . $configuredWindow->end_at->format('d/m/Y H:i') . '.';
        }

        return 'El proceso de ' . $processLabel . ' no esta disponible en la fecha actual segun el calendario academico.';
    }


    public static function unavailableActivityViewData(
        string $processKey,
        ?string $activityLabel = null,
        ?Carbon $reference = null,
        string $redirectRoute = 'home',
        int $redirectSeconds = 5
    ): array {
        $reference ??= now();
        $activityLabel = $activityLabel ?: mb_strtolower(self::processLabel($processKey));

        return [
            'title' => 'Actividad no disponible',
            'activityLabel' => $activityLabel,
            'message' => 'La actividad de ' . $activityLabel . ' ya no esta disponible en esta fecha. Consulte el calendario academico de la universidad.',
            'detail' => self::processWindowUnavailableMessage($processKey, $reference),
            'calendarUrl' => 'https://www.udi.edu.co/admisiones#calendario-academico',
            'redirectUrl' => route($redirectRoute),
            'redirectSeconds' => $redirectSeconds,
        ];
    }

    public static function ensureProcessWindowOpenOrFail(string $processKey, ?Carbon $reference = null): AcademicProcessWindow
    {
        $window = self::currentWindowForProcess($processKey, $reference);

        if (! $window) {
            abort(403, self::processWindowUnavailableMessage($processKey, $reference));
        }

        return $window;
    }

    public static function recordProjectStage(
        Project $project,
        string $stage,
        ?AcademicPeriod $academicPeriod = null,
        ?int $changedByUserId = null,
        ?string $notes = null,
        array $metadata = []
    ): ProjectStageHistory {
        return ProjectStageHistory::query()->create([
            'project_id' => $project->id,
            'academic_period_id' => $academicPeriod?->id,
            'stage' => $stage,
            'event_at' => now(),
            'changed_by_user_id' => $changedByUserId,
            'notes' => $notes,
            'metadata' => $metadata ?: null,
        ]);
    }
}
