<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for duplicating a static block.
 */
class DuplicateStaticBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_identifier' => 'required|string|max:100|unique:static_blocks,identifier',
        ];
    }

    public function messages(): array
    {
        return [
            'new_identifier.required' => 'New identifier is required.',
            'new_identifier.unique' => 'This identifier is already in use.',
        ];
    }
}
