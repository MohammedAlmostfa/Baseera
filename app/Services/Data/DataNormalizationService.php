<?php

namespace App\Services\Data;

use App\DTOs\Data\NormalizedDatasetDTO;
use Illuminate\Support\Facades\Log;

class DataNormalizationService
{
    private const COLUMN_ALIASES = [
        'revenue' => 'revenue',
        'sales' => 'revenue',
        'sales revenue' => 'revenue',
        'الإيرادات' => 'revenue',
        'الدخل' => 'revenue',

        'cost' => 'cost',
        'costs' => 'cost',
        'التكلفة' => 'cost',
        'التكاليف' => 'cost',

        'profit' => 'profit',
        'profits' => 'profit',
        'الربح' => 'profit',
        'الأرباح' => 'profit',

        'product' => 'product',
        'product name' => 'product',
        'المنتج' => 'product',
    ];

    public function normalize(
        NormalizedDatasetDTO $dataset
    ): NormalizedDatasetDTO {
        Log::info('data.normalization.started', [
            'row_count' => $dataset->rowCount,
            'column_count' => count($dataset->columns),
        ]);

        $columns = array_map(
            fn (string $column) => $this->normalizeColumnName($column),
            $dataset->columns
        );

        $rows = array_map(
            fn (array $row) => $this->normalizeRow($row, $dataset->columns, $columns),
            $dataset->rows
        );

        $normalizedDataset = new NormalizedDatasetDTO(
            columns: array_values(array_unique($columns)),
            rows: $rows,
            rowCount: count($rows),
        );

        Log::info('data.normalization.completed', [
            'row_count' => $normalizedDataset->rowCount,
            'column_count' => count($normalizedDataset->columns),
            'columns' => $normalizedDataset->columns,
        ]);

        return $normalizedDataset;
    }

    private function normalizeColumnName(string $column): string
    {
        $normalized = mb_strtolower(trim($column));

        return self::COLUMN_ALIASES[$normalized] ?? $normalized;
    }

    private function normalizeRow(
        array $row,
        array $originalColumns,
        array $normalizedColumns
    ): array {
        $normalizedRow = [];

        foreach ($originalColumns as $index => $column) {
            $normalizedColumn = $normalizedColumns[$index];

            $normalizedRow[$normalizedColumn] = $this->normalizeValue(
                $row[$column] ?? null
            );
        }

        return $normalizedRow;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return null;
            }

            $numericValue = $this->parseNumber($value);

            return $numericValue ?? $value;
        }

        return $value;
    }

    private function parseNumber(string $value): int|float|null
    {
        $cleaned = str_replace(',', '', $value);

        if (! is_numeric($cleaned)) {
            return null;
        }

        return str_contains($cleaned, '.')
            ? (float) $cleaned
            : (int) $cleaned;
    }
}
