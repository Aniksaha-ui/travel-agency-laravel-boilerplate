<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueryLog extends Model
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
