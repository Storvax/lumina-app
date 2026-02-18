<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name', 'description', 'icon', 'color', 'flames_reward', 'is_hidden'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')->withPivot('unlocked_at');
    }
}