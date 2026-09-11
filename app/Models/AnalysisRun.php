<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class AnalysisRun extends Model
{
    protected $fillable = [
        'file_id',
        'status',
        'revenue',
        'cost',
        'profit',
        'profit_margin',
        'row_count',
    ];

    protected function casts(): array
    {
        return [
            'revenue' => 'decimal:2',
            'cost' => 'decimal:2',
            'profit' => 'decimal:2',
            'profit_margin' => 'decimal:2',
            'row_count' => 'integer',
        ];
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
    public function aiAnalysis(): HasOne
{
    return $this->hasOne(AiAnalysis::class);
}
}
