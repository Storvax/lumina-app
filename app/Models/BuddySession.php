<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuddySession extends Model
{
    protected $fillable = ['user_id', 'buddy_id', 'room_id', 'status', 'rating', 'started_at', 'completed_at'];
    protected $casts = ['started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function buddy() { return $this->belongsTo(User::class, 'buddy_id'); }
    public function room() { return $this->belongsTo(Room::class); }
}