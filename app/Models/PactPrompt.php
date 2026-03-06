<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PactPrompt extends Model
{
    protected $fillable = ['question', 'active_date'];

    protected function casts(): array
    {
        return ['active_date' => 'date'];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PactAnswer::class);
    }
}
