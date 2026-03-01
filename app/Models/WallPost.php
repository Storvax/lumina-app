<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WallPost extends Model
{
    protected $fillable = [
        'user_id',
        'image_path',
        'caption',
        'is_sensitive',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_sensitive' => 'boolean',
            'is_approved'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}
