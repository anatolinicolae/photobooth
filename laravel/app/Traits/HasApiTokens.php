<?php

namespace App\Traits;

use App\Models\ApiToken;
use Illuminate\Support\Str;

trait HasApiTokens
{
    /**
     * Get all of the user's API tokens.
     */
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Create a new API token for the user.
     *
     * @param string $name
     * @param array $abilities
     * @param \DateTimeInterface|null $expiresAt
     * @return array Returns ['token' => ApiToken, 'plainTextToken' => string]
     */
    public function createToken(string $name, array $abilities = ['*'], $expiresAt = null): array
    {
        $plainTextToken = Str::random(64);
        
        $token = $this->apiTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return [
            'token' => $token,
            'plainTextToken' => $plainTextToken,
        ];
    }

    /**
     * Get the user's current access token.
     *
     * @return ApiToken|null
     */
    public function currentAccessToken()
    {
        return $this->accessToken ?? null;
    }

    /**
     * Set the user's current access token.
     *
     * @param ApiToken $token
     * @return $this
     */
    public function withAccessToken(ApiToken $token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Revoke all of the user's API tokens.
     *
     * @return void
     */
    public function revokeAllTokens(): void
    {
        $this->apiTokens()->delete();
    }

    /**
     * Revoke a specific token by ID.
     *
     * @param int $tokenId
     * @return bool
     */
    public function revokeToken(int $tokenId): bool
    {
        return $this->apiTokens()->where('id', $tokenId)->delete() > 0;
    }
}
