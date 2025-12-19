<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for importing a static block.
 */
class ImportStaticBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:100|unique:static_blocks,identifier',
            'type' => 'nullable|string|max:50',
            'settings' => 'nullable|array',
            'translations' => 'required|array',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.content' => 'required|string',
        ];
    }
}
