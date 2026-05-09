<?php

namespace Database\Seeders;

class ProfessorsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/professors.csv';

    protected array $uniqueBy = ['card_id'];
}
