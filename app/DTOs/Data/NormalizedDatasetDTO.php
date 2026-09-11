<?php

namespace App\DTOs\Data;

final readonly class NormalizedDatasetDTO
{
    public function __construct(
        public array $columns,
        public array $rows,
        public int $rowCount,
    ) {}
}
