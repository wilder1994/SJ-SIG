<?php

use App\Http\Middleware\BindCurrentContract;
use App\Http\Middleware\EnsurePasswordWasChanged;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceRequestRootUrl;
use App\Http\Middleware\RequireCurrentContract;
use App\Http\Middleware\RestrictTechnicianModules;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            ForceRequestRootUrl::class,
        ]);
        $middleware->alias([
            'contract.bound' => BindCurrentContract::class,
            'user.active' => EnsureUserIsActive::class,
            'password.changed' => EnsurePasswordWasChanged::class,
            'tech.modules' => RestrictTechnicianModules::class,
            'contract.required' => RequireCurrentContract::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
