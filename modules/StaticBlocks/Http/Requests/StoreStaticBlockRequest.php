<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for storing a new static block.
 */
class StoreStaticBlockRequest extends FormRequest
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
            'translations' => 'required|array|min:1',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.content' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Block identifier is required.',
            'identifier.unique' => 'This identifier is already in use.',
            'translations.required' => 'At least one translation is required.',
        ];
    }
}
