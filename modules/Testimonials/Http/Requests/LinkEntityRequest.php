<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for linking a testimonial to an entity.
 */
class LinkEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_type' => 'required|string|in:service,product,project',
            'entity_id' => 'required|uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'entity_type.required' => 'Entity type is required.',
            'entity_type.in' => 'Entity type must be one of: service, product, project.',
            'entity_id.required' => 'Entity ID is required.',
            'entity_id.uuid' => 'Entity ID must be a valid UUID.',
        ];
    }
}
