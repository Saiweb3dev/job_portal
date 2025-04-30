<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // This middleware runs after auth:sanctum middleware, so $request->user() is already authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if the authenticated user has any of the required roles
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Access denied. You do not have the required permission.',
            ], 403);
        }

        return $next($request);
    }
}
