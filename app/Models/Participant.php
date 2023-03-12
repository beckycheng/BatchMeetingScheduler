<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'username',
        'preferred_time',
        'scheduled_time',
        'preferred_time_updated_at',
    ];

    protected $casts = [
        'preferred_time' => 'array',
    ];

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting');
    }
}
