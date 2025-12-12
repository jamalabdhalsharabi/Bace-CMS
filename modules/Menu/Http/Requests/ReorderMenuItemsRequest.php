<?php

declare(strict_types=1);

namespace Modules\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'uuid', 'exists:menu_items,id'],
        ];
    }
}
