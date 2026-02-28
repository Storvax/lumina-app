<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationLog extends Model
{
    protected $fillable = ['user_id', 'room_id', 'action', 'target_user_id', 'details'];

    public function user() { return $this->belongsTo(User::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function targetUser() { return $this->belongsTo(User::class, 'target_user_id'); }
}