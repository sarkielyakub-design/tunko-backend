<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | CORS
        |--------------------------------------------------------------------------
        */
        $middleware->append(HandleCors::class);

        /*
        |--------------------------------------------------------------------------
        | API Authentication
        |--------------------------------------------------------------------------
        | Never redirect API users to a web login page.
        | Always return JSON instead.
        */
        $middleware->redirectGuestsTo(function (Request $request) {
            return null;
        });

    })

    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Always return JSON for API routes
        |--------------------------------------------------------------------------
        */
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*')
        );

        /*
        |--------------------------------------------------------------------------
        | Authentication Exception
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (
            AuthenticationException $exception,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        });

    })

    ->create();