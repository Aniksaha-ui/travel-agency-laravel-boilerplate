<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class seats extends Model
{
    protected $fillable = [
        'vehicle_id', 'seat_number', 'seat_class','seat_type'
    ];
}
