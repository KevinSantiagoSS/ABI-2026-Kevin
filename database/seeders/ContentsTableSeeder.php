<?php

namespace Database\Seeders;

class ContentsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/contents.csv';

    protected array $uniqueBy = ['name'];
}
