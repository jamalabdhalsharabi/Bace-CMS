<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating an existing testimonial.
 */
class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_name' => 'sometimes|string|max:255',
            'author_title' => 'nullable|string|max:255',
            'author_company' => 'nullable|string|max:255',
            'author_avatar_id' => 'nullable|uuid|exists:media,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'translations' => 'sometimes|array',
            'translations.*.content' => 'required_with:translations|string',
        ];
    }
}
