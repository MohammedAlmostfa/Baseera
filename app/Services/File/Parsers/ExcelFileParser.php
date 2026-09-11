<?php

namespace App\Services\File\Parsers;

use App\Contracts\File\FileParserInterface;
use App\DTOs\File\ParsedFileDTO;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ExcelFileParser implements FileParserInterface
{
    public function supports(string $type): bool
    {
        return in_array($type, ['xlsx', 'xls'], true);
    }

    public function parse(File $file): ParsedFileDTO
    {
        Log::info('file.excel.parse.started', [
            'file_id' => $file->id,
            'path' => $file->path,
        ]);

        $path = storage_path(
            'app/private/' . $file->path
        );

        if (! is_file($path)) {
            throw new RuntimeException(
                'ملف Excel غير موجود.'
            );
        }

        $spreadsheet = IOFactory::load($path);

        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(
            null,
            true,
            true,
            true
        );

        if (empty($rows)) {
            throw new RuntimeException(
                'ملف Excel فارغ.'
            );
        }

        $rawHeaders = array_shift($rows);

        $headers = array_map(
            fn ($header) => trim((string) $header),
            array_values($rawHeaders)
        );

        $normalizedRows = [];

        foreach ($rows as $row) {
            $values = array_values($row);

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $normalizedRows[] = array_combine(
                $headers,
                array_pad(
                    $values,
                    count($headers),
                    null
                )
            );
        }

        $parsedFile = new ParsedFileDTO(
            fileType: 'xlsx',
            headers: $headers,
            rows: $normalizedRows,
            rowCount: count($normalizedRows),
            columnCount: count($headers),
        );

        Log::info('file.excel.parse.completed', [
            'file_id' => $file->id,
            'row_count' => $parsedFile->rowCount,
            'column_count' => $parsedFile->columnCount,
        ]);

        return $parsedFile;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
