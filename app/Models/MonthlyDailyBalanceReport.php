<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyDailyBalanceReport extends Model
{
    protected $fillable = [
        'report_name',
        'file_path',
        'report_month',
    ];

    protected $casts = [
        'report_month' => 'date',
    ];
}
