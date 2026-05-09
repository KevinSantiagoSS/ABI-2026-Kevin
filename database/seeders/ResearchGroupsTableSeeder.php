<?php

namespace Database\Seeders;

class ResearchGroupsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/research_groups.csv';

    protected array $uniqueBy = ['name'];
}
