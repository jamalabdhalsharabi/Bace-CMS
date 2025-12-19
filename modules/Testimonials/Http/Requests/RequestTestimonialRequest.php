<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for requesting a testimonial from a client.
 */
class RequestTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_email' => 'required|email|max:255',
            'client_name' => 'required|string|max:255',
            'message' => 'nullable|string|max:2000',
            'entity_type' => 'nullable|string|max:50',
            'entity_id' => 'nullable|uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'client_email.required' => 'Client email is required.',
            'client_email.email' => 'Please provide a valid email address.',
            'client_name.required' => 'Client name is required.',
        ];
    }
}
