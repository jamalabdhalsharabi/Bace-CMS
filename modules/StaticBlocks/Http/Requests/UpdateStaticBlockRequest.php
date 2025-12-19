<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating a static block.
 */
class UpdateStaticBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
            'translations' => 'sometimes|array',
            'translations.*.title' => 'required_with:translations|string|max:255',
            'translations.*.content' => 'required_with:translations|string',
        ];
    }
}
