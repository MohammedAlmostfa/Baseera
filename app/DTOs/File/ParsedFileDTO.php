<?php

namespace App\DTOs\File;

final readonly class ParsedFileDTO
{
    public function __construct(
        public string $fileType,
        public array $headers,
        public array $rows,
        public int $rowCount,
        public int $columnCount,
    ) {}
}
