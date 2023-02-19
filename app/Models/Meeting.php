<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'subject',
        'moderator',
        'duration',
        'deadline',
        'timeslots',
    ];

    public $incrementing = false;
    protected static function boot()
    {
        parent::boot();
        static::creating(function (Meeting $meeting) {
            $meeting->id = Str::random(7);
        });
    }

    protected $casts = [
        'timeslots' => 'array',
    ];

    public function moderatorUser()
    {
        return $this->belongsTo('App\Models\User', 'moderator', 'id');
    }

    public function participants()
    {
        return $this->hasMany('App\Models\Participant');
    }
}
