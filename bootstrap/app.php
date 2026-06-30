<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\TerminateReservations;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withMiddleware(function ($middleware) {

        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'locale' => App\Http\Middleware\LocaleMiddleware::class,
        ]);
    })
    ->withMiddleware(function ($middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);
    })

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(TerminateReservations::class);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
