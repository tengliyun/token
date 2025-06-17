<?php

namespace Tengliyun\Token\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tengliyun\Token\JWToken;

interface HasApiToken
{
    /**
     * Get all the access tokens for the user.
     *
     * @return MorphMany
     */
    public function tokens(): MorphMany;

    /**
     * Create a new personal access token for the user.
     *
     * @param string $name
     * @param string $package
     * @param array  $scopes
     *
     * @return JWToken
     */
    public function createToken(string $name, string $package, array $scopes = ['*']): JWToken;

    /**
     * Get the current auth token being used by the user.
     *
     * @return AuthToken|null
     */
    public function authToken(): ?AuthToken;

    /**
     * Set the current auth token for the user.
     *
     * @param AuthToken $authToken
     *
     * @return static
     */
    public function withAuthToken(AuthToken $authToken): static;

    /**
     * Determine if the current auth token has one or more of the given scopes.
     *
     * @param array|string $scopes One or more scopes to check against the current token.
     *
     * @return bool True if the token has at least one of the given scopes.
     */
    public function tokenCan(array|string $scopes): bool;
}
