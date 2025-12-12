<?php

declare(strict_types=1);

namespace Modules\Comments\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'author' => [
                'id' => $this->user_id,
                'name' => $this->author_name,
                'email' => $this->when($request->user()?->id === $this->user_id, $this->author_email),
                'avatar' => $this->user?->avatar_url ?? null,
            ],
            'content' => $this->content,
            'status' => $this->status,
            'likes_count' => $this->likes_count,
            'is_pinned' => $this->is_pinned,
            'replies' => $this->when($this->replies->isNotEmpty(), fn () =>
                CommentResource::collection($this->replies)
            ),
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
