<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'pseudonym' => $this->pseudonym,
            'avatar' => $this->avatar,
            'flames' => $this->flames,
            'flame_level' => $this->flame_level,
            'current_streak' => $this->current_streak,
            'role' => $this->role,
            'onboarding_completed' => $this->onboarding_tours !== null && count((array) $this->onboarding_tours) > 0,
            'bio' => $this->bio,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
