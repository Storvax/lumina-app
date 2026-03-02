<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['name', 'description', 'enabled', 'rollout_percentage', 'allowed_users'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'allowed_users' => 'array',
        ];
    }
}
