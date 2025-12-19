<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for embedding a block in a page.
 */
class EmbedInPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_id' => 'required|uuid',
            'position' => 'nullable|string|max:50',
        ];
    }
}
