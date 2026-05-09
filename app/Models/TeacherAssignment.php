<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_period_id',
        'program_id',
        'professor_id',
        'assigned_hours',
        'observations',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'academic_period_id' => 'integer',
        'program_id' => 'integer',
        'professor_id' => 'integer',
        'assigned_hours' => 'integer',
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

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class, 'professor_id', 'id');
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
