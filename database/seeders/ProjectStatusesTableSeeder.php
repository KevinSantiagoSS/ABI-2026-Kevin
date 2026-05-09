<?php

namespace Database\Seeders;

class ProjectStatusesTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/project_statuses.csv';

    protected array $uniqueBy = ['name'];
}
