<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class query_logs_table extends Model
{
    protected $fillable = [
        'request_id',
        'sql',
        'bindings',
        'time_ms',
        'connection',
        'url',
        'method',
        'user_id',
        'is_slow',
    ];
}
