<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteCommentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'vote' => ['required', 'in:up,down'],
        ];
    }
}
