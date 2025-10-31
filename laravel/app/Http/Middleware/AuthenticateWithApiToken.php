<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $ability
     */
    public function handle(Request $request, Closure $next, string $ability = null): Response
    {
        // Get token from Authorization header (Bearer token)
        $bearerToken = $request->bearerToken();
        
        if (!$bearerToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API token required. Please provide a valid Bearer token in the Authorization header.'
            ], 401);
        }

        // Hash the provided token to compare with stored hash
        $hashedToken = hash('sha256', $bearerToken);

        // Find the token in the database
        $apiToken = ApiToken::where('token', $hashedToken)
            ->active()
            ->first();

        if (!$apiToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired API token.'
            ], 401);
        }

        // Check if token is expired
        if ($apiToken->isExpired()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'API token has expired.'
            ], 401);
        }

        // Check if token has required ability
        if ($ability && !$apiToken->can($ability)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "This token does not have the required '{$ability}' permission."
            ], 403);
        }

        // Load the user and set the current access token
        $user = $apiToken->user;
        $user->withAccessToken($apiToken);

        // Update last used timestamp (in background to avoid performance impact)
        $apiToken->touchLastUsedAt();

        // Set the authenticated user
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
