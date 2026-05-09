<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'evaluation_criteria',
        'thematic_area_id',
        'project_status_id',
        'proposal_academic_period_id',
        'assignment_academic_period_id',
        'proposed_at',
        'assigned_at',
    ];

    protected $casts = [
        'thematic_area_id' => 'integer',
        'project_status_id' => 'integer',
        'proposal_academic_period_id' => 'integer',
        'assignment_academic_period_id' => 'integer',
        'proposed_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = is_null($value)
            ? null
            : Str::of($value)->squish()->title()->toString();
    }

    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id', 'id');
    }

    public function thematicArea(): BelongsTo
    {
        return $this->belongsTo(ThematicArea::class, 'thematic_area_id', 'id');
    }

    public function proposalAcademicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'proposal_academic_period_id', 'id');
    }

    public function assignmentAcademicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'assignment_academic_period_id', 'id');
    }

    public function scopePendingReviewDueToAge(Builder $query, ?int $thresholdPeriods = null): Builder
    {
        return app(\App\Services\Projects\ProjectAgeReviewService::class)
            ->applyPendingReviewDueToAge($query, $thresholdPeriods);
    }

    public function isPendingReviewDueToAge(?int $thresholdPeriods = null): bool
    {
        return app(\App\Services\Projects\ProjectAgeReviewService::class)
            ->shouldFlag($this, $thresholdPeriods);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class, 'project_id', 'id');
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(ProjectStageHistory::class, 'project_id', 'id')->orderByDesc('event_at');
    }

    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class, 'professor_project', 'project_id', 'professor_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_project', 'project_id', 'student_id');
    }

    public function contentFrameworkProjects(): HasMany
    {
        return $this->hasMany(ContentFrameworkProject::class, 'project_id', 'id');
    }

    public function contentFrameworks(): BelongsToMany
    {
        return $this->belongsToMany(ContentFramework::class, 'content_framework_project', 'project_id', 'content_framework_id');
    }
}
