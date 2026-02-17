<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationLog extends Model
{
    protected $fillable = ['moderator_id', 'target_user_id', 'target_type', 'target_id', 'action', 'reason'];

    public function moderator() { return $this->belongsTo(User::class, 'moderator_id'); }
    public function targetUser() { return $this->belongsTo(User::class, 'target_user_id'); }
    public function target() { return $this->morphTo(); }
}