<?php

namespace Database\Seeders;

class ContentFrameworksTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/content_frameworks.csv';

    protected array $uniqueBy = ['framework_id', 'name'];
}
