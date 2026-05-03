<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure all API routes return JSON responses
        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle JWT authentication exceptions - return 401 instead of 500
        $exceptions->render(function (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                ], 404);
            }

            // JWT token exceptions
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token.',
                ], 401);
            }
            
            // Token not provided
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has been blacklisted.',
                ], 401);
            }

            // Missing or invalid auth
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Missing or invalid auth
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please provide a valid token.',
                ], 401);
            }

            // Validation exceptions
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->validator->errors(),
                ], 422);
            }

            if (str_starts_with(request()->path(), 'api/')) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Server error.',
                ], 500);
            }

            // Return null to let Laravel handle other exceptions normally
            return null;
        });
    })->create();
