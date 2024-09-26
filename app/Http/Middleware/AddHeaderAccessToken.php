<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddHeaderAccessToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Access-Control-Allow-Origin', '*');

        if ($request->has('access_token')) {
            $request->headers->set('Authorization', 'Bearer '.$request->get('access_token'));
        }

        return $next($request);
    }
}
