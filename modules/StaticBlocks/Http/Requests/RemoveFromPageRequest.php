<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for removing a block from a page.
 */
class RemoveFromPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_id' => 'required|uuid',
        ];
    }
}
