<?php

namespace Tengliyun\Token;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tengliyun\Token\Contracts\AuthToken;

trait HasApiTokens
{
    protected ?AuthToken $accessToken = null;

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
     * @return PersonalAccessToken
     */
    public function createToken(string $name, string $package, array $scopes = ['*']): PersonalAccessToken
    {
        $personalAccessToken = app(PersonalAccessToken::class);

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

        return tap($personalAccessToken, function ($factory) use ($tokenable) {
            $tokenable->update($factory->toArray());
        });
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return AuthToken|null
     */
    public function token(): ?AuthToken
    {
        return $this->accessToken;
    }

    /**
     * Set the current access token for the user.
     *
     * @param AuthToken $authToken
     *
     * @return static
     */
    public function withAccessToken(AuthToken $authToken): static
    {
        $this->accessToken = $authToken;

        return $this;
    }

    /**
     * Determine if the current access token has one or more of the given scopes.
     *
     * @param array|string $scopes One or more scopes to check against the current token.
     *
     * @return bool True if the token has at least one of the given scopes.
     */
    public function tokenCan(array|string $scopes): bool
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];

        foreach ($scopes as $scope) {
            if ($this->accessToken?->can($scope)) {
                return true;
            }
        }

        return false;
    }
}
