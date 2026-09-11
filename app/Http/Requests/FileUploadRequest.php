<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:csv,xlsx,pdf,docx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'الملف مطلوب.',
            'file.file' => 'الملف المرفوع غير صالح.',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 20MB.',
            'file.mimes' => 'نوع الملف غير مدعوم.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('file.upload.validation_failed', [
            'errors' => $validator->errors()->toArray(),
        ]);

        throw new ValidationException($validator);
    }
}
