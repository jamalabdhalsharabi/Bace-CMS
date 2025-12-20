<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Index Comments Request.
 *
 * Validates input parameters for retrieving comments associated with
 * a specific commentable entity. Used for displaying comment threads
 * on articles, products, or other commentable content.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $commentable_type The fully qualified class name of commentable entity
 * @property string $commentable_id The UUID of the commentable entity
 * @property int|null $per_page Number of comments per page (optional, defaults to 20)
 */
class IndexCommentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as reading comments is a public operation.
     * No special authorization is required to view comments.
     *
     * @return bool Always true to allow comment retrieval
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates the commentable entity identifier to ensure comments
     * are retrieved for a valid, existing entity.
     *
     * Rules include:
     * - commentable_type: Required string identifying the entity class
     * - commentable_id: Required UUID of the specific entity
     *
     * @return array<string, string> Validation rules array
     */
    public function rules(): array
    {
        return [
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|uuid',
        ];
    }
}
