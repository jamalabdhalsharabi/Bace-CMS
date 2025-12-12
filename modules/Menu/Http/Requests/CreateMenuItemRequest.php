<?php

declare(strict_types=1);

namespace Modules\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:page,article,taxonomy,custom,module'],
            'title' => ['required', 'array', 'min:1'],
            'title.en' => ['required', 'string', 'max:100'],
            'title.*' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'uuid', 'exists:menu_items,id'],
            'linkable_id' => ['nullable', 'uuid'],
            'linkable_type' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'in:_self,_blank'],
            'icon' => ['nullable', 'string', 'max:50'],
            'css_class' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'conditions' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Menu item type is required.',
            'type.in' => 'Invalid menu item type.',
            'title.required' => 'Title is required.',
            'title.en.required' => 'English title is required.',
        ];
    }
}
