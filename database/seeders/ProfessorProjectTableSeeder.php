<?php

namespace Database\Seeders;

class ProfessorProjectTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/professor_project.csv';

    protected array $uniqueBy = ['professor_id', 'project_id'];
}
