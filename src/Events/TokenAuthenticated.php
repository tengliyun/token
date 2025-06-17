<?php

namespace Tengliyun\Token\Events;

use Illuminate\Queue\SerializesModels;
use Tengliyun\Token\Contracts\AuthToken;
use Tengliyun\Token\Contracts\HasApiToken;

class TokenAuthenticated
{
    use SerializesModels;

    public function __construct(
        public HasApiToken $tokenable,
        public AuthToken   $token,
    )
    {
        //
    }
}
