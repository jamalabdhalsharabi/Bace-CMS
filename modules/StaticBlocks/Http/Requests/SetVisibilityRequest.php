<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for setting block visibility rules.
 */
class SetVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rules' => 'required|array',
            'rules.pages' => 'nullable|array',
            'rules.user_roles' => 'nullable|array',
            'rules.conditions' => 'nullable|array',
        ];
    }
}
