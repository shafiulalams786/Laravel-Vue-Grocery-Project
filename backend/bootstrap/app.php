<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ── CORS must run FIRST (prepend, not append) ─────────────────────
        // If it runs after auth middleware, a 401 response won't have CORS
        // headers and the browser reports "CORS Failed" instead of 401.
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        // ── Exclude /api/* from CSRF (we use Bearer token auth) ───────────
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie',
        ]);

        // ── Middleware aliases ────────────────────────────────────────────
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // ── Trust all proxies (for Nginx / load balancer) ─────────────────
        $middleware->trustProxies(at: '*');

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        });

    })->create();
