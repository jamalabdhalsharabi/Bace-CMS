<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Lock Comments Request.
 *
 * Validates input data for locking/unlocking comments on commentable entities.
 * Used by administrators to control commenting functionality on specific content
 * such as articles, products, or other commentable models.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $model_type The fully qualified class name of commentable model
 * @property string $model_id The UUID of the commentable model instance
 */
class LockCommentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as authorization is handled by middleware.
     * Only administrators should have access to lock/unlock endpoints.
     *
     * @return bool Always true to allow request processing
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates the commentable entity identification parameters.
     * 
     * Rules include:
     * - model_type: Required string representing the commentable model class
     * - model_id: Required UUID of the commentable model instance
     *
     * @return array<string, array<int, string>> Validation rules array
     */
    public function rules(): array
    {
        return [
            'model_type' => ['required', 'string', 'max:255'],
            'model_id' => ['required', 'uuid'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string> Custom validation messages
     */
    public function messages(): array
    {
        return [
            'model_type.required' => 'Model type is required.',
            'model_id.required' => 'Model ID is required.',
            'model_id.uuid' => 'Invalid model ID format.',
        ];
    }
}
