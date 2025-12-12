<?php

declare(strict_types=1);

namespace Modules\Users\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'profile' => $this->when($this->profile, fn () => [
                'first_name' => $this->profile->first_name,
                'last_name' => $this->profile->last_name,
                'full_name' => $this->profile->full_name,
                'phone' => $this->profile->phone,
                'avatar_url' => $this->profile->avatar_url,
                'initials' => $this->profile->initials,
                'bio' => $this->profile->bio,
                'locale' => $this->profile->locale,
                'timezone' => $this->profile->timezone,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
