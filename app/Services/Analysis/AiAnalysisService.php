<?php

namespace App\Services\Analysis;

use App\Ai\Agents\BusinessAnalysisAgent;
use App\DTOs\Analysis\BusinessAnalysisContextDTO;
use App\Models\AiAnalysis;
use App\Models\AnalysisRun;
use Laravel\Ai\Enums\Lab;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiAnalysisService
{
    public function analyze(
        AnalysisRun $analysisRun,
        BusinessAnalysisContextDTO $context
    ): AiAnalysis {
        Log::info('ai.analysis.started', [
            'analysis_run_id' => $analysisRun->id,
            'provider' => 'gemini',
        ]);

        $aiAnalysis = $analysisRun->aiAnalysis()->create([
            'status' => 'processing',
            'provider' => 'gemini',
        ]);

        Log::info('ai.analysis.record.created', [
            'analysis_run_id' => $analysisRun->id,
            'ai_analysis_id' => $aiAnalysis->id,
            'status' => 'processing',
        ]);

        try {
            $response = (new BusinessAnalysisAgent)->prompt(
                $this->buildPrompt($context),
                provider: Lab::Gemini,
            );

            $aiAnalysis->update([
                'status' => 'completed',
                'model' => config('ai.providers.gemini.models.text.default'),
                'summary' => $response['summary'],
                'score' => $response['score'],
                'risks' => $response['risks'],
                'opportunities' => $response['opportunities'],
                'recommendations' => $response['recommendations'],
            ]);

            Log::info('ai.analysis.completed', [
                'analysis_run_id' => $analysisRun->id,
                'ai_analysis_id' => $aiAnalysis->id,
                'provider' => 'gemini',
                'model' => $aiAnalysis->model,
                'score' => $aiAnalysis->score,
            ]);

            return $aiAnalysis;
        } catch (Throwable $exception) {
            $aiAnalysis->update([
                'status' => 'failed',
            ]);

            Log::error('ai.analysis.failed', [
                'analysis_run_id' => $analysisRun->id,
                'ai_analysis_id' => $aiAnalysis->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function buildPrompt(
        BusinessAnalysisContextDTO $context
    ): string {
        $metrics = $context->metrics;

        return <<<PROMPT
Analyze the following business metrics for Basira.

Business Metrics:

Revenue: {$metrics->revenue}
Cost: {$metrics->cost}
Profit: {$metrics->profit}
Profit Margin: {$metrics->profitMargin}%
Rows analyzed: {$metrics->rowCount}

Provide a business-focused analysis.

Focus on:
- Overall business health
- Important risks
- Potential opportunities
- Practical recommendations

Do not invent any additional numerical data.
PROMPT;
    }
}
