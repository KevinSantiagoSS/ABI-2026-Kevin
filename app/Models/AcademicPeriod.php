<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicPeriod extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'status',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function processWindows(): HasMany
    {
        return $this->hasMany(AcademicProcessWindow::class, 'academic_period_id', 'id');
    }

    public function proposalProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'proposal_academic_period_id', 'id');
    }

    public function assignedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'assignment_academic_period_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query
            ->orderBy('start_date')
            ->orderBy('end_date')
            ->orderBy('id');
    }

    public function canBeActivatedOn(?CarbonInterface $date = null): bool
    {
        if (! $this->start_date || ! $this->end_date) {
            return false;
        }

        $date ??= now();

        return $date->betweenIncluded(
            $this->start_date->copy()->startOfDay(),
            $this->end_date->copy()->endOfDay()
        );
    }

    public static function lastInSequence(?int $ignoreId = null): ?self
    {
        return static::query()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();
    }

    public static function overlapsRange(string $startDate, string $endDate, ?int $ignoreId = null): bool
    {
        return static::query()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists();
    }

    public static function adjacentPeriodsFor(self $period): array
    {
        $periods = static::query()
            ->chronological()
            ->get(['id', 'start_date', 'end_date']);

        $index = $periods->search(fn (self $registeredPeriod) => (int) $registeredPeriod->id === (int) $period->id);

        return [
            'previous' => $index === false || $index === 0 ? null : $periods->get($index - 1),
            'next' => $index === false || $index === ($periods->count() - 1) ? null : $periods->get($index + 1),
        ];
    }

    public static function formDateConstraints(?self $period = null): array
    {
        if ($period && $period->exists) {
            ['previous' => $previous, 'next' => $next] = static::adjacentPeriodsFor($period);

            $minDate = $previous?->end_date?->copy()->addDay();
            $maxDate = $next?->start_date?->copy()->subDay();

            return [
                'start_min' => $minDate?->toDateString(),
                'end_min' => $minDate?->toDateString(),
                'start_max' => $maxDate?->toDateString(),
                'end_max' => $maxDate?->toDateString(),
            ];
        }

        $nextAvailableDate = static::lastInSequence()?->end_date?->copy()->addDay();

        return [
            'start_min' => $nextAvailableDate?->toDateString(),
            'end_min' => $nextAvailableDate?->toDateString(),
            'start_max' => null,
            'end_max' => null,
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_CLOSED => 'Cerrado',
        ];
    }
}
