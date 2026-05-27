<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['api', 'jwt']]
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => \App\Http\Middleware\AuthenticateJwt::class,
            'permission' => \App\Http\Middleware\RequirePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e) {
            if (! request()->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $e instanceof AuthenticationException => 401,
                $e instanceof ValidationException => 422,
                $e instanceof HttpException => $e->getStatusCode(),
                $e instanceof \InvalidArgumentException, $e instanceof \RuntimeException => 400,
                default => 500,
            };

            $detail = $e->getMessage() !== ''
                ? $e->getMessage()
                : match ($status) {
                    401 => 'Unauthenticated.',
                    403 => 'Forbidden.',
                    400 => 'Bad request.',
                    default => 'Internal server error.',
                };

            return \App\Support\ProblemDetails::json($status, $detail);
        });
    })->create();
