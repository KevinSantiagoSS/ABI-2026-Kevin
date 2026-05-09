<?php

namespace Database\Seeders;

class LoadProjectionsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/load_projections.csv';

    protected array $uniqueBy = ['academic_period_id', 'program_id'];

    protected bool|string $timestamps = false;
}
