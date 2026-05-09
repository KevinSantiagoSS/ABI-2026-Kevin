<?php

namespace Database\Seeders;

class StudentProjectTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/student_project.csv';

    protected array $uniqueBy = ['student_id', 'project_id'];
}
