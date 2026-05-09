<?php

namespace Database\Seeders;

class StudentsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/students.csv';

    protected array $uniqueBy = ['card_id'];
}
