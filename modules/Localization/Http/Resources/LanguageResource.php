<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'native_name' => $this->native_name,
            'direction' => $this->direction,
            'flag' => $this->flag,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'is_rtl' => $this->isRtl(),
            'ordering' => $this->ordering,
        ];
    }
}
