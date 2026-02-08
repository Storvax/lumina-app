<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyLog extends Model
{
    protected $fillable = ['user_id', 'mood_level', 'tags', 'note', 'log_date'];

    // O Laravel converte automaticamente o JSON para Array e vice-versa
    protected $casts = [
        'tags' => 'array',
        'log_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
