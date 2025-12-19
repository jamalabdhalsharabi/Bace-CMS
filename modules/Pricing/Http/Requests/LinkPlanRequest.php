<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_type' => 'required|string|in:product,service,event,project',
            'entity_id' => 'required|uuid',
            'is_required' => 'nullable|boolean',
        ];
    }
}
