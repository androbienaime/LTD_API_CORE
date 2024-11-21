<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            if ($e instanceof TokenExpiredException) {
                return response()->json(['message' => 'Token has expired'], 401);
            } else if ($e instanceof TokenInvalidException) {
                return response()->json(['message' => 'Token is invalid'], 401);
            } else {
                return response()->json(['message' => 'Token not found'], 401);
            }
        }
        
        return $next($request);
    }
}
