<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Vote Comment Request.
 *
 * Validates input data for voting on comments (upvote or downvote).
 * Used when authenticated users interact with comment voting system
 * to express approval or disapproval of comment content.
 *
 * @package Modules\Comments\Http\Requests
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @property string $vote The vote type - either 'up' for upvote or 'down' for downvote
 */
class VoteCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Always returns true as comment voting is open to all authenticated users.
     * Specific authorization logic (like preventing voting on own comments)
     * is handled in controllers and services.
     *
     * @return bool Always true to allow voting attempts
     */
    public function authorize(): bool 
    { 
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Validates the vote type to ensure it's either an upvote or downvote.
     * Only these two vote types are supported by the commenting system.
     * 
     * Rules include:
     * - vote: Required string that must be either 'up' or 'down'
     *
     * @return array<string, array<int, string>> Validation rules array
     */
    public function rules(): array
    {
        return [
            'vote' => ['required', 'in:up,down'],
        ];
    }
}
