<?php

namespace App\Services\File\Parsers;

use App\Contracts\File\FileParserInterface;
use App\DTOs\File\ParsedFileDTO;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CsvFileParser implements FileParserInterface
{
    public function supports(string $type): bool
    {
        return $type === 'csv';
    }

    public function parse(File $file): ParsedFileDTO
    {
        Log::info('file.csv.parse.started', [
            'file_id' => $file->id,
            'path' => $file->path,
        ]);

        $handle = fopen(
            storage_path('app/private/' . $file->path),
            'r'
        );

        if ($handle === false) {
            throw new RuntimeException('تعذر قراءة ملف CSV.');
        }

        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);

            throw new RuntimeException('ملف CSV فارغ.');
        }

        $headers = array_map(
            fn ($header) => trim($header),
            $headers
        );

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim($row[0]) === '') {
                continue;
            }

            $rows[] = array_combine($headers, $row);
        }

        fclose($handle);

        $parsedFile = new ParsedFileDTO(
            fileType: 'csv',
            headers: $headers,
            rows: $rows,
            rowCount: count($rows),
            columnCount: count($headers),
        );

        Log::info('file.csv.parse.completed', [
            'file_id' => $file->id,
            'row_count' => $parsedFile->rowCount,
            'column_count' => $parsedFile->columnCount,
        ]);

        return $parsedFile;
    }
}
