<?php

namespace App\Services\Metrics;

use App\DTOs\Data\NormalizedDatasetDTO;
use App\DTOs\Metrics\MetricsResultDTO;
use Illuminate\Support\Facades\Log;

class MetricsService
{
    public function calculate(
        NormalizedDatasetDTO $dataset
    ): MetricsResultDTO {
        Log::info('metrics.calculation.started', [
            'row_count' => $dataset->rowCount,
            'column_count' => count($dataset->columns),
        ]);

        $revenue = $this->sumColumn(
            $dataset->rows,
            'revenue'
        );

        $cost = $this->sumColumn(
            $dataset->rows,
            'cost'
        );

        $profit = $revenue - $cost;

        $profitMargin = $revenue > 0
            ? ($profit / $revenue) * 100
            : 0;

        $metrics = new MetricsResultDTO(
            revenue: $revenue,
            cost: $cost,
            profit: $profit,
            profitMargin: round($profitMargin, 2),
            rowCount: $dataset->rowCount,
        );

        Log::info('metrics.calculation.completed', [
            'row_count' => $metrics->rowCount,
            'revenue' => $metrics->revenue,
            'cost' => $metrics->cost,
            'profit' => $metrics->profit,
            'profit_margin' => $metrics->profitMargin,
        ]);

        return $metrics;
    }

    private function sumColumn(
        array $rows,
        string $column
    ): float {
        return array_sum(
            array_map(
                fn (array $row) => (float) ($row[$column] ?? 0),
                $rows
            )
        );
    }
}
