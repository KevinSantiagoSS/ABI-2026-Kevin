<?php

namespace Database\Seeders;

class ProjectsTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/projects.csv';

    protected array $uniqueBy = ['title'];

    protected bool|string $timestamps = false;
}
