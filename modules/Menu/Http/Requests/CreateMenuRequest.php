<?php

declare(strict_types=1);

namespace Modules\Menu\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:50', 'unique:menus,slug'],
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Menu slug is required.',
            'slug.unique' => 'This slug is already in use.',
            'name.required' => 'Menu name is required.',
        ];
    }
}
