<?php

namespace Tengliyun\Token\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class TokenUserProvider implements UserProvider
{
    /**
     * Create a new passport user provider.
     *
     * @param UserProvider $provider
     */
    public function __construct(
        protected UserProvider $provider,
    )
    {
        //
    }

    /**
     * @inheritdoc
     *
     * @return Authenticatable|null
     */
    public function retrieveById(mixed $identifier): ?Authenticatable
    {
        return $this->provider->retrieveById($identifier);
    }

    /**
     * @inheritdoc
     *
     * @return Authenticatable|null
     */
    public function retrieveByToken(mixed $identifier, $token): ?Authenticatable
    {
        return $this->provider->retrieveByToken($identifier, $token);
    }

    /**
     * @inheritdoc
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $this->provider->updateRememberToken($user, $token);
    }

    /**
     * @inheritdoc
     *
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        return $this->provider->retrieveByCredentials($credentials);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return $this->provider->validateCredentials($user, $credentials);
    }
}
