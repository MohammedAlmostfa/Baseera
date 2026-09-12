<?php

namespace App\Jobs;

use App\DTOs\Analysis\BusinessAnalysisContextDTO;
use App\Enums\FileStatus;
use App\Models\AnalysisRun;
use App\Services\Analysis\AiAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AIAnalysisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 3600;

    public function __construct(
        private readonly int $analysisRunId
    ) {}

    public function handle(
        AiAnalysisService $aiAnalysisService,
    ): void {
        $startedAt = microtime(true);

        $analysisRun = AnalysisRun::with('file')
            ->findOrFail($this->analysisRunId);

        Log::info('ai.analysis.job.started', [
            'analysis_run_id' => $analysisRun->id,
            'file_id' => $analysisRun->file_id,
        ]);

        try {
            /*
             * Rebuild the AI context from persisted metrics.
             * We don't need to process the original file again.
             */
            $context = new BusinessAnalysisContextDTO(
                metrics: $analysisRun->toMetricsResultDTO(),
            );

            $aiAnalysisService->analyze(
                $analysisRun,
                $context
            );

            $analysisRun->update([
                'status' => 'completed',
            ]);

            $analysisRun->file->update([
                'status' => FileStatus::ANALYZED,
            ]);

            Log::info('ai.analysis.job.completed', [
                'analysis_run_id' => $analysisRun->id,
                'file_id' => $analysisRun->file_id,
                'duration_ms' => (int) (
                    (microtime(true) - $startedAt) * 1000
                ),
            ]);
        } catch (Throwable $exception) {
            $analysisRun->update([
                'status' => 'failed',
            ]);

            $analysisRun->file->update([
                'status' => FileStatus::FAILED,
            ]);

            Log::error('ai.analysis.job.failed', [
                'analysis_run_id' => $analysisRun->id,
                'file_id' => $analysisRun->file_id,
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
