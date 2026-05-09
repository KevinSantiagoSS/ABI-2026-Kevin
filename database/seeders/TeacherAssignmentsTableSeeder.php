<?php

namespace Database\Seeders;

class TeacherAssignmentsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/teacher_assignments.csv';

    protected array $uniqueBy = ['academic_period_id', 'program_id', 'professor_id'];

    protected bool|string $timestamps = false;
}
