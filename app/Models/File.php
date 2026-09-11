<?php

namespace App\Models;

use App\Enums\FileStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'path',
        'type',
        'size',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FileStatus::class,
            'size' => 'integer',
        ];
    }
    public function analysisRuns(): HasMany
{
    return $this->hasMany(AnalysisRun::class);
}
}
