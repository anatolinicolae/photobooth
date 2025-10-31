<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiTokenController extends Controller
{
    /**
     * Generate a new API token for a user.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|in:upload,delete,*',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        
        $abilities = $request->input('abilities', ['*']);
        $expiresAt = $request->input('expires_at');

        $result = $user->createToken(
            $request->name,
            $abilities,
            $expiresAt ? new \DateTime($expiresAt) : null
        );

        return response()->json([
            'message' => 'API token created successfully',
            'token' => [
                'id' => $result['token']->id,
                'name' => $result['token']->name,
                'abilities' => $result['token']->abilities,
                'expires_at' => $result['token']->expires_at?->toIso8601String(),
                'created_at' => $result['token']->created_at->toIso8601String(),
            ],
            'plainTextToken' => $result['plainTextToken'],
            'warning' => 'Store this token securely. It will not be shown again.'
        ], 201);
    }

    /**
     * List all API tokens for a user.
     * 
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        
        $tokens = $user->apiTokens()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->toIso8601String(),
                    'expires_at' => $token->expires_at?->toIso8601String(),
                    'created_at' => $token->created_at->toIso8601String(),
                    'is_expired' => $token->isExpired(),
                ];
            });

        return response()->json([
            'tokens' => $tokens
        ]);
    }

    /**
     * Revoke a specific API token.
     * 
     * @param int $userId
     * @param int $tokenId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revoke($userId, $tokenId)
    {
        $user = User::findOrFail($userId);
        
        $revoked = $user->revokeToken($tokenId);

        if (!$revoked) {
            return response()->json([
                'error' => 'Token not found or already revoked'
            ], 404);
        }

        return response()->json([
            'message' => 'API token revoked successfully'
        ]);
    }

    /**
     * Revoke all API tokens for a user.
     * 
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll($userId)
    {
        $user = User::findOrFail($userId);
        $user->revokeAllTokens();

        return response()->json([
            'message' => 'All API tokens revoked successfully'
        ]);
    }
}
