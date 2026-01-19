<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\QueryLog;

class QueryMonitor
{
    protected static array $queries = [];

    public function handle($request, Closure $next)
    {
        DB::listen(function ($query) use ($request) {

            $sqlWithBindings = $this->getRealQuery($query->sql, $query->bindings);
            self::$queries[] = [
                'sql'        => $sqlWithBindings,
                'bindings'   => json_encode($query->bindings),
                'time_ms'    => $query->time,
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'user_id'    => auth()->id(),
                'is_slow'    => $query->time > 100 ? 1 : 0,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        });

        return $next($request);
    }

    public function terminate($request, $response)
    {
        DB::table('query_logs')->insert(self::$queries);
    }



     protected function getRealQuery($sql, $bindings)
    {
        foreach ($bindings as $binding) {
            // quote string values
            $value = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

}
