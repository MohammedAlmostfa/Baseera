<?php

namespace App\Services\File;

use App\DTOs\Data\NormalizedDatasetDTO;
use App\Models\File;
use App\Services\Data\DataCleaningService;
use App\Services\Data\DataNormalizationService;
use Illuminate\Support\Facades\Log;

class FileProcessingService
{
    public function __construct(
        private readonly FileParserResolver $parserResolver,
        private readonly DataCleaningService $cleaningService,
        private readonly DataNormalizationService $normalizationService,
    ) {}

    public function process(File $file): NormalizedDatasetDTO
    {
        Log::info('file.processing.parser_started', [
            'file_id' => $file->id,
            'type' => $file->type,
        ]);

        $parser = $this->parserResolver->resolve($file);

        $parsedDataset = $parser->parse($file);

        Log::info('file.processing.parsed', [
            'file_id' => $file->id,
            'type' => $parsedDataset->fileType,
            'row_count' => $parsedDataset->rowCount,
            'column_count' => $parsedDataset->columnCount,
        ]);

        $cleanedDataset = $this->cleaningService->clean(
            $parsedDataset
        );

        Log::info('file.processing.cleaned', [
            'file_id' => $file->id,
            'row_count' => $cleanedDataset->rowCount,
        ]);

        $normalizedDataset = $this->normalizationService->normalize(
            $cleanedDataset
        );

        Log::info('file.processing.normalized', [
            'file_id' => $file->id,
            'row_count' => $normalizedDataset->rowCount,
            'column_count' => count($normalizedDataset->columns),
        ]);

        return $normalizedDataset;
    }
}
