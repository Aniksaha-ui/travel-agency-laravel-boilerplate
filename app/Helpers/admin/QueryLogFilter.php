<?php

namespace App\Helpers\admin;

use Carbon\Carbon;

class QueryLogFilter
{
    public static function fromDate(): Carbon
    {
        return now()->subDays(
            config('query_monitor.days')
        )->startOfDay();
    }
}
