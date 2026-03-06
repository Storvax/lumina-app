<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PactAnswer extends Model
{
    protected $fillable = ['user_id', 'pact_prompt_id', 'answer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(PactPrompt::class, 'pact_prompt_id');
    }
}
