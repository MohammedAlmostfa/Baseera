<?php

namespace App\Services\File\Parsers;

use App\Contracts\File\FileParserInterface;
use App\DTOs\File\ParsedFileDTO;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CsvFileParser implements FileParserInterface
{
    private const DEFAULT_HEADERS = [
        3 => ['product', 'revenue', 'cost'],
    ];

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

        $firstRow = fgetcsv($handle);

        if ($firstRow === false) {
            fclose($handle);

            throw new RuntimeException('ملف CSV فارغ.');
        }

        $firstRow = array_map(
            fn ($value) => $this->cleanCsvValue($value),
            $firstRow
        );

        $hasHeaderRow = $this->looksLikeHeaderRow($firstRow);
        $headers = $hasHeaderRow
            ? $this->cleanHeaders($firstRow)
            : $this->defaultHeadersFor($firstRow);

        if (! $hasHeaderRow) {
            Log::warning('file.csv.headers_missing', [
                'file_id' => $file->id,
                'column_count' => count($firstRow),
                'headers' => $headers,
            ]);
        }

        $rows = $hasHeaderRow ? [] : [$this->combineRow($headers, $firstRow)];

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map(
                fn ($value) => $this->cleanCsvValue($value),
                $row
            );

            if (count($row) === 1 && trim($row[0]) === '') {
                continue;
            }

            $rows[] = $this->combineRow($headers, $row);
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

    private function looksLikeHeaderRow(array $row): bool
    {
        $knownHeaders = [
            'product',
            'product name',
            'revenue',
            'sales',
            'sales revenue',
            'cost',
            'costs',
            'profit',
            'profits',
            'المنتج',
            'الإيرادات',
            'التكلفة',
            'الربح',
        ];

        return count(array_intersect(
            array_map(fn ($value) => mb_strtolower(trim($value)), $row),
            $knownHeaders
        )) > 0;
    }

    private function cleanHeaders(array $headers): array
    {
        return array_map(
            fn ($header) => trim((string) $header, " \t\r\n\xEF\xBB\xBF"),
            $headers
        );
    }

    private function cleanCsvValue(mixed $value): string
    {
        return trim((string) $value, " \t\r\n\xEF\xBB\xBF");
    }

    private function defaultHeadersFor(array $row): array
    {
        $headers = self::DEFAULT_HEADERS[count($row)] ?? null;

        if ($headers === null) {
            throw new RuntimeException(
                'تعذر تحديد أعمدة CSV. أضف صف headers مثل: product,revenue,cost.'
            );
        }

        return $headers;
    }

    private function combineRow(array $headers, array $row): array
    {
        if (count($headers) !== count($row)) {
            throw new RuntimeException(
                'عدد القيم في صف CSV لا يطابق عدد الأعمدة.'
            );
        }

        return array_combine($headers, $row);
    }
}
