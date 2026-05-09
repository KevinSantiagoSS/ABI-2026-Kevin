<?php

namespace Database\Seeders;

class ContentVersionTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/content_version.csv';

    protected array $uniqueBy = ['version_id', 'content_id'];
}
