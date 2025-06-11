<?php

namespace Tengliyun\Token\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface AuthToken
{
    public function tokenable(): MorphTo;

    public function findToken(string $token);

    public function can(string $ability): bool;

    public function cant(string $ability): bool;
}
