<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PactPrompt extends Model
{
    protected $fillable = ['body', 'category', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PactAnswer::class);
    }
}
