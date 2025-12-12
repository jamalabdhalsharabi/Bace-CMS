<?php

declare(strict_types=1);

namespace Modules\Media\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            'collection' => $this->collection,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_human' => $this->human_readable_size,
            'dimensions' => $this->dimensions,
            'url' => $this->url,
            'urls' => $this->when($this->isImage(), fn () => [
                'original' => $this->url,
                'thumbnail' => $this->getUrl('thumbnail'),
                'small' => $this->getUrl('small'),
                'medium' => $this->getUrl('medium'),
                'large' => $this->getUrl('large'),
            ]),
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_audio' => $this->isAudio(),
            'is_document' => $this->isDocument(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
