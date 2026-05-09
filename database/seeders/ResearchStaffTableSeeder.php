<?php

namespace Database\Seeders;

class ResearchStaffTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/research_staff.csv';

    protected array $uniqueBy = ['card_id'];
}
