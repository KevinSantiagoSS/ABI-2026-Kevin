<?php

namespace Database\Seeders;

class ProgramsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/programs.csv';

    protected array $uniqueBy = ['code'];
}
