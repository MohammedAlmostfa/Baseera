<?php

namespace App\Services\File;

use App\Contracts\File\FileParserInterface;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FileParserResolver
{
    /**
     * @param iterable<FileParserInterface> $parsers
     */
    public function __construct(
        private readonly iterable $parsers
    ) {}

    public function resolve(File $file): FileParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($file->type)) {
                Log::info('file.parser.resolved', [
                    'file_id' => $file->id,
                    'file_type' => $file->type,
                    'parser' => $parser::class,
                ]);

                return $parser;
            }
        }

        Log::warning('file.parser.unsupported', [
            'file_id' => $file->id,
            'file_type' => $file->type,
        ]);

        throw new RuntimeException(
            "نوع الملف غير مدعوم: {$file->type}"
        );
    }
}
