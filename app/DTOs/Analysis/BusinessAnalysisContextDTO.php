<?php

namespace App\DTOs\Analysis;

use App\DTOs\Metrics\MetricsResultDTO;

final readonly class BusinessAnalysisContextDTO
{
    public function __construct(
        public MetricsResultDTO $metrics,
    ) {}
}
