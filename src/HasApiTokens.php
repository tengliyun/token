<?php

namespace Tengliyun\Token;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tengliyun\Token\Contracts\AuthToken;

trait HasApiTokens
{
    protected ?AuthToken $authToken = null;

    /**
     * Get all the access tokens for the user.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(Token::tokenModel(), 'tokenable');
    }

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param string $package
     * @param array  $scopes
     *
     * @return JWToken
     */
    public function createToken(string $name, string $package, array $scopes = ['*']): JWToken
    {
        $personalAccessToken = JWToken::getInstance();

        if (!$this->exists) {
            return $personalAccessToken;
        }

        $data = [
            'name'                    => $name,
            'package'                 => $package,
            'access_token'            => now(),
            'refresh_token'           => now(),
            'access_token_expire_at'  => now()->add(Token::accessTokensExpireIn())->toDateTimeImmutable(),
            'refresh_token_expire_at' => now()->add(Token::refreshTokensExpireIn())->toDateTimeImmutable(),
            'scopes'                  => $scopes,
        ];

        $tokenable = $this->tokens()->create($data);

        $personalAccessToken = $personalAccessToken->accessToken($tokenable)->refreshToken($tokenable);

        return tap($personalAccessToken, function (JWToken $personalAccessToken) use ($tokenable) {
            $tokenable->update($personalAccessToken->toArray());
        });
    }

    /**
     * Get the current auth token being used by the user.
     *
     * @return AuthToken|null
     */
    public function authToken(): ?AuthToken
    {
        return $this->authToken;
    }

    /**
     * Set the current auth token for the user.
     *
     * @param AuthToken $authToken
     *
     * @return static
     */
    public function withAuthToken(AuthToken $authToken): static
    {
        $this->authToken = $authToken;

        return $this;
    }

    /**
     * Determine if the current auth token has one or more of the given scopes.
     *
     * @param array|string $scopes One or more scopes to check against the current token.
     *
     * @return bool True if the token has at least one of the given scopes.
     */
    public function tokenCan(array|string $scopes): bool
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];

        foreach ($scopes as $scope) {
            if ($this->authToken?->can($scope)) {
                return true;
            }
        }

        return false;
    }
}
