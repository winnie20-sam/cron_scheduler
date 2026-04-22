<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledJob extends Model
{
    protected $fillable = [
        'name',
        'schedule',
        'description',
        'status',
        'last_run_at',
        'duration_ms',
        'last_error',
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
    ];
}
