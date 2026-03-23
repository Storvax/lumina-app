<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mood_level' => $this->mood_level,
            'tags' => $this->tags ?? [],
            'note' => $this->note,
            'cbt_insight' => $this->cbt_insight,
            'log_date' => $this->log_date?->toDateString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
