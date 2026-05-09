<?php

namespace Database\Seeders;

class AcademicPeriodsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/academic_periods.csv';

    protected array $uniqueBy = ['code'];

    protected bool|string $timestamps = false;
}
