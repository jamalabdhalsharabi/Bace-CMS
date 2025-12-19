<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for importing testimonials from external sources.
 */
class ImportTestimonialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => 'required|string|in:google,facebook,trustpilot,csv',
            'data' => 'required|array|min:1',
            'data.*.author_name' => 'nullable|string|max:255',
            'data.*.company' => 'nullable|string|max:255',
            'data.*.rating' => 'nullable|integer|min:1|max:5',
            'data.*.external_id' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'source.required' => 'Import source is required.',
            'source.in' => 'Source must be one of: google, facebook, trustpilot, csv.',
            'data.required' => 'Import data is required.',
            'data.min' => 'At least one testimonial is required for import.',
        ];
    }
}
