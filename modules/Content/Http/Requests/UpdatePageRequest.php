<?php

declare(strict_types=1);

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Page Request.
 *
 * Validates input data for updating existing CMS pages with multi-language support.
 *
 * @package Modules\Content\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 */
class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always true (authorization handled by middleware)
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'uuid', 'exists:pages,id'],
            'template' => ['nullable', 'string', 'max:50'],
            'featured_image_id' => ['nullable', 'uuid', 'exists:media,id'],
            'is_homepage' => ['nullable', 'boolean'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['nullable', 'string', 'max:255'],
            'translations.*.content' => ['nullable', 'string'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:60'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:160'],
            'translations.*.meta_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }
}
