<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class BusinessAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<PROMPT
You are Basira's business intelligence analyst.

Your job is to analyze business metrics and provide practical
decision-support insights.

Rules:

1. Never invent numerical facts.
2. Use only the metrics provided in the prompt.
3. Do not recalculate metrics incorrectly.
4. Clearly distinguish facts from interpretations.
5. Identify meaningful business risks.
6. Identify realistic business opportunities.
7. Give actionable recommendations.
8. Recommendations must be practical for a small or medium business.
9. Keep the analysis concise and useful for decision makers.
10. Return the requested structured format only.
PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'summary' => $schema
                ->string()
                ->required(),

            'score' => $schema
                ->integer()
                ->min(0)
                ->max(100)
                ->required(),

            'risks' => $schema
                ->array()
                ->items(
                    $schema->object(fn ($schema) => [
                        'title' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'severity' => $schema
                            ->string()
                            ->enum(['low', 'medium', 'high'])
                            ->required(),
                    ])
                )
                ->required(),

            'opportunities' => $schema
                ->array()
                ->items(
                    $schema->object(fn ($schema) => [
                        'title' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'impact' => $schema
                            ->string()
                            ->enum(['low', 'medium', 'high'])
                            ->required(),
                    ])
                )
                ->required(),

            'recommendations' => $schema
                ->array()
                ->items(
                    $schema->object(fn ($schema) => [
                        'title' => $schema->string()->required(),
                        'description' => $schema->string()->required(),
                        'priority' => $schema
                            ->string()
                            ->enum(['low', 'medium', 'high'])
                            ->required(),
                    ])
                )
                ->required(),
        ];
    }
}
