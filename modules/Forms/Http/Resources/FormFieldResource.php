<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'placeholder' => $this->placeholder,
            'default_value' => $this->default_value,
            'options' => $this->options,
            'is_required' => $this->is_required,
            'ordering' => $this->ordering,
        ];
    }
}
