<?php

namespace Database\Seeders;

class ContentFrameworkProjectTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/content_framework_project.csv';

    protected array $uniqueBy = ['project_id', 'content_framework_id'];
}
