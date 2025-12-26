<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Authentication Middleware.
 *
 * Authenticates API requests using Sanctum personal access tokens.
 */
class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.',
            ], 401);
        }

        // Check if token is expired
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired.',
            ], 401);
        }

        // Set the authenticated user
        $user = $accessToken->tokenable;
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 401);
        }

        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();

        // Set the current access token on the user
        $user->withAccessToken($accessToken);
        
        // Set the user on the request - this enables request()->user()
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
