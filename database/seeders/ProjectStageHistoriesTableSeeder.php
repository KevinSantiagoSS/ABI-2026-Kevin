<?php

namespace Database\Seeders;

class ProjectStageHistoriesTableSeeder extends CsvUpsertSeeder
{
    protected string $file = '/database/seeders/csvs/project_stage_histories.csv';

    protected array $uniqueBy = ['project_id', 'stage', 'event_at'];

    protected bool|string $timestamps = false;
}
