<?php

namespace Database\Seeders;

class VersionsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/versions.csv';

    protected array $uniqueBy = ['project_id', 'created_at'];

    protected bool|string $timestamps = false;
}
