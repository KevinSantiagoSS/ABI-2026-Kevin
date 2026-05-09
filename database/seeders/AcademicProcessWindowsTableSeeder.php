<?php

namespace Database\Seeders;

class AcademicProcessWindowsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/academic_process_windows.csv';

    protected array $uniqueBy = ['academic_period_id', 'process_key'];

    protected bool|string $timestamps = false;
}
