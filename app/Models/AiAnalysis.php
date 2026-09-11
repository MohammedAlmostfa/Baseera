<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $fillable = [
        'analysis_run_id',
        'status',
        'provider',
        'model',
        'summary',
        'score',
        'risks',
        'opportunities',
        'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'risks' => 'array',
            'opportunities' => 'array',
            'recommendations' => 'array',
        ];
    }

    public function analysisRun(): BelongsTo
    {
        return $this->belongsTo(AnalysisRun::class);
    }
}
