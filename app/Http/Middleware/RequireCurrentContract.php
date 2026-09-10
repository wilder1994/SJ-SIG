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

        if ($request->routeIs(
            'dashboard',
            'clients.*',
            'users.*',
            'profile.*',
            'people.index',
            'sites.index',
            'documents.index',
            'parafiscals.index',
            'electronics.index',
            'services.index',
            'novelties.index',
            'operations.index',
        )) {
            return $next($request);
        }

        $user = $request->user();
        $target = $user?->role->canManageClients()
            ? route('clients.index')
            : route('dashboard');

        return redirect($target)->with(
            'status',
            'Este módulo necesita un cliente. Crea el primero en Clientes o pide a Administración que lo dé de alta.',
        );
    }
}
