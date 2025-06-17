<?php

namespace Tengliyun\Token\Auth\Access;

trait Authorizable
{
    /**
     * Determine if the token has a given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function can(string $ability): bool
    {
        return in_array(
                '*', $this->getAttribute('scopes')
            ) || array_key_exists(
                $ability, array_flip($this->getAttribute('scopes'))
            );
    }

    /**
     * Determine if the token is missing a given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function cant(string $ability): bool
    {
        return !$this->can($ability);
    }
}
