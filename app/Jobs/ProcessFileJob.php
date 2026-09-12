<?php
namespace App\Jobs;

use App\Enums\FileStatus;
use App\Models\File;
use App\Services\Analysis\AnalysisRunService;
use App\Services\File\FileProcessingService;
use App\Services\Metrics\MetricsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessFileJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 3600;

    public function __construct(
        private readonly int $fileId
    ) {}

    public function handle(
        FileProcessingService $processingService,
        MetricsService $metricsService,
        AnalysisRunService $analysisRunService,
    ): void {
        $startedAt = microtime(true);

        $file = null;
        $analysisRun = null;

        Log::info('file.processing.started', [
            'file_id' => $this->fileId,
        ]);

        try {
            $file = File::findOrFail($this->fileId);

            $file->update([
                'status' => FileStatus::PROCESSING,
            ]);

            Log::info('file.status.updated', [
                'file_id' => $file->id,
                'status' => FileStatus::PROCESSING->value,
            ]);

            /*
             * Parse → Clean → Normalize
             */
            $dataset = $processingService->process($file);

            Log::info('file.processing.dataset_ready', [
                'file_id' => $file->id,
                'row_count' => $dataset->rowCount,
                'column_count' => count($dataset->columns),
            ]);

            /*
             * Calculate deterministic business metrics.
             */
            $metrics = $metricsService->calculate($dataset);

            Log::info('file.processing.metrics_ready', [
                'file_id' => $file->id,
                'row_count' => $metrics->rowCount,
                'revenue' => $metrics->revenue,
                'cost' => $metrics->cost,
                'profit' => $metrics->profit,
                'profit_margin' => $metrics->profitMargin,
            ]);

            /*
             * Persist the metrics snapshot.
             */
            $analysisRun = $analysisRunService->create(
                $file,
                $metrics
            );

            Log::info('file.analysis_run.created', [
                'file_id' => $file->id,
                'analysis_run_id' => $analysisRun->id,
            ]);

            /*
             * Processing is complete.
             * AI analysis is handled by a separate queue job.
             */
            $file->update([
                'status' => FileStatus::PROCESSED,
            ]);

            Log::info('file.processing.completed', [
                'file_id' => $file->id,
                'analysis_run_id' => $analysisRun->id,
                'status' => FileStatus::PROCESSED->value,
                'duration_ms' => (int) (
                    (microtime(true) - $startedAt) * 1000
                ),
            ]);

            /*
             * Dispatch AI analysis only after the processing
             * pipeline has completed successfully.
             */
            AIAnalysisJob::dispatch($analysisRun->id);

            Log::info('ai.analysis.job.dispatched', [
                'file_id' => $file->id,
                'analysis_run_id' => $analysisRun->id,
            ]);
        } catch (Throwable $exception) {
            if ($analysisRun !== null) {
                $analysisRun->update([
                    'status' => 'failed',
                ]);
            }

            if ($file !== null) {
                $file->update([
                    'status' => FileStatus::FAILED,
                ]);
            }

            Log::error('file.processing.failed', [
                'file_id' => $this->fileId,
                'analysis_run_id' => $analysisRun?->id,
                'status_updated' => $file !== null,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'duration_ms' => (int) (
                    (microtime(true) - $startedAt) * 1000
                ),
            ]);

            throw $exception;
        }
    }
}

