<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveSeoMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seoable_type' => 'required|string',
            'seoable_id' => 'required|uuid',
            'locale' => 'nullable|string|size:2',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
            'robots' => 'nullable|string|max:50',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:200',
            'og_image' => 'nullable|url',
        ];
    }
}
