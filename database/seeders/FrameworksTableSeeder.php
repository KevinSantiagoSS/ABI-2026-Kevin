<?php

namespace Database\Seeders;

class FrameworksTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/frameworks.csv';

    protected array $uniqueBy = ['name'];
}
