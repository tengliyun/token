<?php

namespace Tengliyun\Token;

use Tengliyun\Token\Contracts\AuthToken;

trait HasApiTokens
{
    protected AuthToken $accessToken;

    protected function claims(): array
    {
        return [

        ];
    }

    public function createToken(string $name, string $package, array $scopes = [])
    {

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
}
