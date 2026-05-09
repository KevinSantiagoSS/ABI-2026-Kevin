<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoadProjection extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period_id',
        'program_id',
        'projected_pg1_students',
        'projected_pg1_groups',
        'projected_pg2_students',
        'projected_pg2_groups',
        'pg1_weekly_hours',
        'pg2_weekly_hours',
        'total_weekly_hours',
        'observations',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'academic_period_id' => 'integer',
        'program_id' => 'integer',
        'projected_pg1_students' => 'integer',
        'projected_pg1_groups' => 'integer',
        'projected_pg2_students' => 'integer',
        'projected_pg2_groups' => 'integer',
        'pg1_weekly_hours' => 'integer',
        'pg2_weekly_hours' => 'integer',
        'total_weekly_hours' => 'integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => 'integer',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id', 'id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id', 'id');
    }
}
