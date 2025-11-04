<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn() => route('admin.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 CSRF token mismatch error
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->is('admin*') || $request->routeIs('admin.*')) {
                if (Auth::check()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'Session expired. Please login again.',
                        'redirect' => route('admin.login')
                    ], 419);
                }

                return redirect()
                    ->route('admin.login')
                    ->with('error', 'Session expired. Please login again.');
            }

            // Untuk route non-admin, kembalikan response default
            return null;
        });
    })->create();
