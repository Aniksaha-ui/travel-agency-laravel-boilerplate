<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class vehicles extends Model
{
    protected $fillable = [
        'vehicle_type', 'vehicle_name', 'total_seats','route_id'
    ];
}
