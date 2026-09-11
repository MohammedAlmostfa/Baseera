<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Services\File\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    public function __construct(
        private readonly FileUploadService $fileUploadService
    ) {}

    public function store(FileUploadRequest $request): JsonResponse
    {
        Log::info('file.upload.requested', [
            'original_name' => $request->file('file')->getClientOriginalName(),
            'size' => $request->file('file')->getSize(),
            'mime_type' => $request->file('file')->getMimeType(),
        ]);

        $file = $this->fileUploadService->upload(
            $request->file('file')
        );

        Log::info('file.upload.response_sent', [
            'file_id' => $file->id,
            'status' => $file->status->value,
        ]);

        return response()->json([
            'message' => 'تم رفع الملف بنجاح وبدأت معالجته.',
            'data' => [
                'id' => $file->id,
                'name' => $file->original_name,
                'type' => $file->type,
                'size' => $file->size,
                'status' => $file->status->value,
            ],
        ], 201);
    }
}
