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
    ];

    protected $casts = [
        'preferred_times' => 'array',
    ];

    public function meeting()
    {
        return $this->belongsTo('App\Models\Meeting');
    }
}
