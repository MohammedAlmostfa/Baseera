<?php

namespace App\DTOs\Metrics;

final readonly class MetricsResultDTO
{
    public function __construct(
        public float $revenue,
        public float $cost,
        public float $profit,
        public float $profitMargin,
        public int $rowCount,
    ) {}
}
