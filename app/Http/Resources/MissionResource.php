<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'icon' => $this->icon,
            'progress' => $this->when($this->pivot, $this->pivot->progress),
            'completed_at' => $this->when($this->pivot, $this->pivot->completed_at),
            'assigned_date' => $this->when($this->pivot, $this->pivot->assigned_date),
        ];
    }
}
