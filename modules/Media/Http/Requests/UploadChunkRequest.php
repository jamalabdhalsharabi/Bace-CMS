<?php

declare(strict_types=1);

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadChunkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'chunk' => ['required', 'file'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1'],
            'upload_id' => ['required', 'string'],
            'filename' => ['required', 'string'],
            'folder_id' => ['nullable', 'uuid'],
        ];
    }
}
