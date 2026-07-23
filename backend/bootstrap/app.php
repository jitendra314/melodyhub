<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\EmailAlreadyVerifiedException;
use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpRateLimitException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\EmailNotVerifiedException;
use App\Exceptions\OtpNotVerifiedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {

            if ($request->is('api/*')) {
                return null;
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /**
         * Validation Exceptions
         */
        $exceptions->render(function (
            ValidationException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        });

        /**
         * Invalid OTP Exception
         */
        $exceptions->render(function (
            InvalidOtpException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 422);
        });

        $exceptions->render(function (
            OtpNotVerifiedException $e,
            Request $request
        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        });

        /**
         * OTP Expired Exception
         */
        $exceptions->render(function (
            OtpExpiredException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 422);
        });

        /**
         * Resend OTP Exception
         */
        $exceptions->render(function (
            OtpCooldownException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 429);
        });

        $exceptions->render(function (
            OtpRateLimitException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 429);
        });

        /**
         * Email Already Verified Exception
         */
        $exceptions->render(function (
            EmailAlreadyVerifiedException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 409);
        });

        /**
         * Authentication Exceptions
         */
        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'errors'  => null,
            ], 401);
        });

        /**
         * Invalid Credentials Exception
         */
        $exceptions->render(function (
            InvalidCredentialsException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 401);
        });

        /**
         * Email Not Verified Exception
         */
        $exceptions->render(function (
            EmailNotVerifiedException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => null,
            ], 403);
        });

        /**
         * Model Not Found Exceptions
         */
        $exceptions->render(function (
            ModelNotFoundException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'errors'  => null,
            ], 404);
        });

        /**
         * HTTP Exceptions
         */
        $exceptions->render(function (
            HttpException $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            $message = match ($e->getStatusCode()) {
                400 => 'Bad request.',
                401 => 'Authentication required.',
                403 => 'Forbidden.',
                404 => 'Resource not found.',
                405 => 'Method not allowed.',
                419 => 'Page expired.',
                422 => 'Validation failed.',
                429 => 'Too many requests.',
                default => 'HTTP error.',
            };

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => null,
            ], $e->getStatusCode());
        });

        /**
         * Unhandled Exceptions
         */
        $exceptions->render(function (
            \Throwable $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => app()->isProduction()
                    ? 'Something went wrong. Please try again later.'
                    : $e->getMessage(),
                'errors' => app()->isLocal()
                    ? [
                        'exception' => class_basename($e),
                        'file'      => basename($e->getFile()),
                        'line'      => $e->getLine(),
                    ]
                    : null,
            ], 500);
        });
    })
    ->create();
