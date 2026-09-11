<?php

namespace App\Services\Data;

use App\DTOs\File\ParsedFileDTO;
use App\DTOs\Data\NormalizedDatasetDTO;
use Illuminate\Support\Facades\Log;

class DataCleaningService
{
    public function clean(ParsedFileDTO $dataset): NormalizedDatasetDTO
    {
        Log::info('data.cleaning.started', [
            'file_type' => $dataset->fileType,
            'row_count' => $dataset->rowCount,
            'column_count' => $dataset->columnCount,
        ]);

        $columns = $this->cleanColumns($dataset->headers);

        $rows = array_map(
            fn (array $row) => $this->cleanRow($row),
            $dataset->rows
        );

        $cleanedDataset = new NormalizedDatasetDTO(
            columns: $columns,
            rows: $rows,
            rowCount: count($rows),
        );

        Log::info('data.cleaning.completed', [
            'file_type' => $dataset->fileType,
            'row_count' => $cleanedDataset->rowCount,
            'column_count' => count($cleanedDataset->columns),
        ]);

        return $cleanedDataset;
    }

    private function cleanColumns(array $columns): array
    {
        return array_map(
            fn ($column) => trim((string) $column),
            $columns
        );
    }

    private function cleanRow(array $row): array
    {
        return array_map(
            function ($value) {
                if (is_string($value)) {
                    $value = trim($value);

                    return $value === '' ? null : $value;
                }

                return $value;
            },
            $row
        );
    }
}
