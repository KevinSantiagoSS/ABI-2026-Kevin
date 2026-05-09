<?php

namespace Database\Seeders;

class ThematicAreasTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/thematic_areas.csv';

    protected array $uniqueBy = ['investigation_line_id', 'name'];
}
