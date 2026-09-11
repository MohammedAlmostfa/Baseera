<?php

namespace App\Services\Analysis;

use App\DTOs\Metrics\MetricsResultDTO;
use App\Models\AnalysisRun;
use App\Models\File;
use Illuminate\Support\Facades\Log;

class AnalysisRunService
{
    public function create(
        File $file,
        MetricsResultDTO $metrics
    ): AnalysisRun {
        Log::info('analysis.run.creation.started', [
            'file_id' => $file->id,
            'row_count' => $metrics->rowCount,
        ]);

        $analysisRun = $file->analysisRuns()->create([
            'status' => 'completed',
            'revenue' => $metrics->revenue,
            'cost' => $metrics->cost,
            'profit' => $metrics->profit,
            'profit_margin' => $metrics->profitMargin,
            'row_count' => $metrics->rowCount,
        ]);

        Log::info('analysis.run.creation.completed', [
            'file_id' => $file->id,
            'analysis_run_id' => $analysisRun->id,
            'status' => $analysisRun->status,
        ]);

        return $analysisRun;
    }
}
