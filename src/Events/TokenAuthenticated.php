<?php

namespace Tengliyun\Token\Events;

use Tengliyun\Token\Contracts\AuthToken;
use Tengliyun\Token\Contracts\HasApiToken;

class TokenAuthenticated
{
    public function __construct(
        public HasApiToken $tokenable,
        public AuthToken   $token,
    )
    {
        //
    }
}
