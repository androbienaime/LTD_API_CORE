<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MultiGuardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $debug = [];
        $debug[] = 'Header Auth: ' . $request->header('Authorization');
        $debug[] = 'Bearer Token: ' . $request->bearerToken();

        try {
            $debug[] = 'account check: ' . var_export($this->auth->guard('account')->check(), true);
        } catch (\Exception $e) {
            $debug[] = 'account ERROR: ' . $e->getMessage();
        }

        try {
            $debug[] = 'account-service check: ' . var_export($this->auth->guard('account-service')->check(), true);
        } catch (\Exception $e) {
            $debug[] = 'account-service ERROR: ' . $e->getMessage();
        }

        // Écrit dans un fichier temporaire
        file_put_contents(
            storage_path('logs/jwt_debug.txt'),
            implode("\n", $debug) . "\n",
            FILE_APPEND
        );       
        // Session web (admin)
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // Session account (Filament shop)
        if (Auth::guard('account')->check()) {
            return $next($request);
        }

        // JWT account-service (API)
        if ($request->bearerToken() && Auth::guard('account-service')->check()) {
            return $next($request);
        }

        // Requête API → retourne 401 au lieu de redirect
        if ($request->expectsJson() || $request->bearerToken()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login');
    }
}