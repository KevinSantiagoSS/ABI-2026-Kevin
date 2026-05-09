<?php

namespace Database\Seeders;

class InvestigationLinesTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/investigation_lines.csv';

    protected array $uniqueBy = ['research_group_id', 'name'];
}
