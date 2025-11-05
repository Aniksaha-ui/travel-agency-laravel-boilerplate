<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class CheckGuide
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user() || !$this->isGuide($request->user())) {
            return response()->json(['message' => 'Authentication failed'], 403);
        }
        return $next($request);
    }

    public function isGuide($user)
    {
        return $user->role == 'guide'  ? true : false;
    }
}
