<?php

declare(strict_types=1);

namespace Modules\Content\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Schedule Article Request.
 *
 * Validates input data for scheduling article publication.
 *
 * @package Modules\Content\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 */
class ScheduleArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool Always true (authorization handled by middleware)
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'publish_at' => 'required|date|after:now',
        ];
    }
}
