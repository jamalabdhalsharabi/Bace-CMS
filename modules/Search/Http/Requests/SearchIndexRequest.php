<?php

declare(strict_types=1);

namespace Modules\Search\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:' . config('search.min_query_length', 2)],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'offset' => ['nullable', 'integer', 'min:0'],
            'filter' => ['nullable', 'string'],
            'sort' => ['nullable', 'array'],
            'sort.*' => ['string'],
        ];
    }
}
