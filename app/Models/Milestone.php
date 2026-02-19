<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = ['user_id', 'title', 'date', 'is_public'];
    protected $casts = [
        'date' => 'date',
        'is_public' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}