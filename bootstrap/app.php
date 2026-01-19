<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => App\Http\Middleware\RoleMiddleware::class,
            'client' => App\Http\Middleware\ClientMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token expiration (419 Page Expired)
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session has expired. Please refresh the page.'], 419);
            }
            return redirect('/')->with('error', 'Your session has expired. Please try again.');
        });

        // Handle undefined routes (Route not defined)
        $exceptions->renderable(function (RouteNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Page not found.'], 404);
            }
            return redirect('/')->with('error', 'The page you requested could not be found.');
        });

        // Handle 404 Not Found
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }
            return redirect('/')->with('error', 'The page you are looking for does not exist.');
        });

        // Handle 403 Forbidden and other HTTP exceptions
        $exceptions->renderable(function (HttpException $e, $request) {
            $statusCode = $e->getStatusCode();

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage() ?: 'An error occurred.'], $statusCode);
            }

            // Redirect specific error codes to welcome page
            if (in_array($statusCode, [403, 419, 500, 503])) {
                $messages = [
                    403 => 'You do not have permission to access this page.',
                    419 => 'Your session has expired. Please try again.',
                    500 => 'Something went wrong. Please try again later.',
                    503 => 'The service is temporarily unavailable. Please try again later.',
                ];
                return redirect('/')->with('error', $messages[$statusCode] ?? 'An error occurred.');
            }

            return null; // Let Laravel handle other HTTP exceptions
        });
    })->create();
