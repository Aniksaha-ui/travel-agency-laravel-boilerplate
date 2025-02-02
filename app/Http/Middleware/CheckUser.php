<?php

namespace App\Http\Middleware;

use Closure;

class CheckUser
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
         if (!$request->user() || !$this->isAdmin($request->user())) {
            return response()->json(['message' => 'Authentication failed'], 403);
        }
        return $next($request);
    }

    public function isAdmin($user){
        return $user->role =='users' ? true : false;
    }
}