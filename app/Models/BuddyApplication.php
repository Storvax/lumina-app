<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuddyApplication extends Model
{
    protected $fillable = ['user_id', 'motivation', 'status'];

    public function user() { return $this->belongsTo(User::class); }
}