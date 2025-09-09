<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        /*api: __DIR__.'/../routes/api.php',*/
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        // Optional alias if you want to use it by name on specific routes
        $middleware->alias([
            'setlocale' => \App\Http\Middleware\SetLocaleFromSession::class,
        ]);

        // Run it for ALL web routes (admin included)
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SetLocaleFromSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withCommands([
        \App\Console\Commands\ReseedCommand::class,
    ])
    ->withEvents(discover: [
        __DIR__.'/../app/Domain/Orders/Listeners',
    ])
    ->create();
