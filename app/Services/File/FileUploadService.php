<?php

namespace App\Services\File;

use App\Enums\FileStatus;
use App\Jobs\ProcessFileJob;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FileUploadService
{
    public function upload(UploadedFile $uploadedFile): File
    {
        $startedAt = microtime(true);

        Log::info('file.upload.started', [
            'original_name' => $uploadedFile->getClientOriginalName(),
            'size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
        ]);

        try {
            $file = DB::transaction(function () use ($uploadedFile) {
                $storedPath = $uploadedFile->store(
                    'company-files',
                    'local'
                );

                Log::info('file.upload.stored', [
                    'path' => $storedPath,
                ]);

                try {
                    $file = File::create([
                        'name' => basename($storedPath),
                        'original_name' => $uploadedFile->getClientOriginalName(),
                        'path' => $storedPath,
                        'type' => $uploadedFile->extension(),
                        'size' => $uploadedFile->getSize(),
                        'status' => FileStatus::UPLOADED,
                    ]);

                    Log::info('file.record.created', [
                        'file_id' => $file->id,
                        'status' => $file->status->value,
                    ]);

                    ProcessFileJob::dispatch($file->id);

                    Log::info('file.processing.dispatched', [
                        'file_id' => $file->id,
                    ]);

                    return $file;
                } catch (Throwable $exception) {
                    Log::error('file.persistence.failed', [
                        'exception' => $exception::class,
                        'message' => $exception->getMessage(),
                        'path' => $storedPath,
                    ]);

                    Storage::disk('local')->delete($storedPath);

                    throw $exception;
                }
            });

            Log::info('file.upload.completed', [
                'file_id' => $file->id,
                'status' => $file->status->value,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return $file;
        } catch (Throwable $exception) {
            Log::error('file.upload.failed', [
                'original_name' => $uploadedFile->getClientOriginalName(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            throw $exception;
        }
    }
}
