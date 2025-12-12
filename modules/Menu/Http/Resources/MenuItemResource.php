<?php

declare(strict_types=1);

namespace Modules\Menu\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'title' => $this->title,
            'titles' => $this->getLocalizedTitles(),
            'url' => $this->url,
            'target' => $this->target,
            'icon' => $this->icon,
            'css_class' => $this->css_class,
            'ordering' => $this->ordering,
            'is_active' => $this->is_active,
            'children' => $this->when($this->children->isNotEmpty(), fn () => 
                MenuItemResource::collection($this->children)
            ),
        ];
    }
}
