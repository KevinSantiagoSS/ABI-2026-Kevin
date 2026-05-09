<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_period_id')
                ->constrained('academic_periods')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('program_id')
                ->constrained('programs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('professor_id')
                ->constrained('professors')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedInteger('assigned_hours')->default(0);
            $table->text('observations')->nullable();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(
                ['academic_period_id', 'program_id', 'professor_id'],
                'teacher_assignments_period_program_professor_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
