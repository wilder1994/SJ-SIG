<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireCurrentContract
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->get('currentContract') !== null) {
            return $next($request);
        }

        abort_unless($request->routeIs(
            'dashboard',
            'clients.*',
            'users.*',
            'profile.*',
        ), 404);

        return $next($request);
    }
}
