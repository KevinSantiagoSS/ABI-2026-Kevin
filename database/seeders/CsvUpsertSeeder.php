<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

abstract class CsvUpsertSeeder extends Seeder
{
    protected string $file;

    protected string $delimiter = ',';

    protected array $uniqueBy = [];

    protected bool|string $timestamps = '2026-04-13 00:00:00';

    protected ?string $table = null;

    public function run(): void
    {
        $path = base_path($this->file);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Unable to read CSV file: {$path}");
        }

        $header = fgetcsv($handle, 0, $this->delimiter);

        if ($header === false) {
            fclose($handle);

            return;
        }

        $rows = [];

        while (($data = fgetcsv($handle, 0, $this->delimiter)) !== false) {
            if ($data === [null] || $data === false) {
                continue;
            }

            $row = array_combine($header, $data);

            if ($row === false) {
                continue;
            }

            $row = array_map([$this, 'normalizeValue'], $row);
            $row = $this->applyTimestamps($row);
            $rows[] = $row;
        }

        fclose($handle);

        if ($rows === []) {
            return;
        }

        $table = $this->table ?? pathinfo($this->file, PATHINFO_FILENAME);
        foreach ($rows as $row) {
            $lookup = [];

            foreach ($this->uniqueBy as $column) {
                $lookup[$column] = $row[$column] ?? null;
            }

            DB::table($table)->updateOrInsert($lookup, $row);
        }
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (! is_string($value)) {
            return $value;
        }

        $upperValue = strtoupper($value);

        return match ($upperValue) {
            'NULL' => null,
            'TRUE' => true,
            'FALSE' => false,
            default => $value,
        };
    }

    protected function applyTimestamps(array $row): array
    {
        if ($this->timestamps === false) {
            return $row;
        }

        $timestamp = $this->timestamps === true
            ? '2026-04-13 00:00:00'
            : $this->timestamps;

        if (! array_key_exists('created_at', $row) || $row['created_at'] === null) {
            $row['created_at'] = $timestamp;
        }

        if (! array_key_exists('updated_at', $row) || $row['updated_at'] === null) {
            $row['updated_at'] = $timestamp;
        }

        return $row;
    }
}
