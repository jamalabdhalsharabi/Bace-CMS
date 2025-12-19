<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for storing a new testimonial.
 */
class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_name' => 'required|string|max:255',
            'author_title' => 'nullable|string|max:255',
            'author_company' => 'nullable|string|max:255',
            'author_avatar_id' => 'nullable|uuid|exists:media,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'nullable|boolean',
            'translations' => 'required|array|min:1',
            'translations.*.content' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required' => 'Author name is required.',
            'translations.required' => 'At least one translation is required.',
            'translations.*.content.required' => 'Content is required for each translation.',
        ];
    }
}
