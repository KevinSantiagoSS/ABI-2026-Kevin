<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'academic_period_id',
        'stage',
        'event_at',
        'changed_by_user_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'academic_period_id' => 'integer',
        'changed_by_user_id' => 'integer',
        'event_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id', 'id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }
}
