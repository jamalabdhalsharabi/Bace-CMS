<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for unlinking a testimonial from an entity.
 */
class UnlinkEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_type' => 'required|string|max:50',
            'entity_id' => 'required|uuid',
        ];
    }
}
