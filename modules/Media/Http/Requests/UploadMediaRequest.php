<?php

declare(strict_types=1);

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('media.max_size', 10240);
        $allowedMimes = $this->getAllowedMimes();

        if ($this->hasFile('files')) {
            return [
                'files' => ['required', 'array', 'max:10'],
                'files.*' => ['file', "max:{$maxSize}", "mimes:{$allowedMimes}"],
                'folder_id' => ['nullable', 'uuid', 'exists:media_folders,id'],
                'collection' => ['nullable', 'string', 'max:50'],
            ];
        }

        return [
            'file' => ['required', 'file', "max:{$maxSize}", "mimes:{$allowedMimes}"],
            'folder_id' => ['nullable', 'uuid', 'exists:media_folders,id'],
            'collection' => ['nullable', 'string', 'max:50'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function getAllowedMimes(): string
    {
        $mimes = config('media.allowed_mimes', []);

        return collect($mimes)->flatten()->implode(',');
    }
}
