<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Jobs\StoreQueryLogsJob;
use Illuminate\Support\Facades\Log;

class QueryMonitor
{
    protected array $queries = [];
    protected string $requestId;
    protected float $totalTime = 0;

    public function handle($request, Closure $next)
    {
        // if (!config('app.query_monitor')) {
        //     return $next($request);
        // }

        $this->requestId = (string) Str::uuid();
        Log::info($this->requestId);
        DB::listen(function ($query) use ($request) {

            // Ignore ultra-fast queries
            if ($query->time < 5) return;

            $this->totalTime += $query->time;

            $this->queries[] = [
                'request_id' => $this->requestId,
                'sql'        => $query->sql,
                'bindings'   => json_encode($this->maskBindings($query->bindings)),
                'time_ms'    => $query->time,
                'connection' => $query->connectionName,
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'user_id'    => Auth::id(),
                'is_slow'    => $query->time > 200,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            dd($this->queries);
        });

        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (!empty($this->queries)) {
            StoreQueryLogsJob::dispatch($this->queries);
        }
    }

    protected function maskBindings(array $bindings): array
    {
        return array_map(
            fn ($b) => is_string($b) ? '***' : $b,
            $bindings
        );
    }
}

