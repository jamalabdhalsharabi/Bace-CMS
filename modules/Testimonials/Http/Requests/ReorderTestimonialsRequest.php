<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for reordering testimonials.
 */
class ReorderTestimonialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order' => 'required|array|min:1',
            'order.*' => 'uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'order.required' => 'Order array is required.',
            'order.array' => 'Order must be an array.',
            'order.*.uuid' => 'Each order item must be a valid UUID.',
        ];
    }
}
