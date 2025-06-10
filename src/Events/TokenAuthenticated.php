<?php

namespace Tengliyun\Token\Events;

use Tengliyun\Token\Contracts\AuthToken;

class TokenAuthenticated
{
    /**
     * The token that was authenticated.
     *
     * @var AuthToken
     */
    public AuthToken $token;

    /**
     * Create a new event instance.
     *
     * @param AuthToken $token
     */
    public function __construct(AuthToken $token)
    {
        $this->token = $token;
    }
}
