<?php

namespace Tengliyun\Token\Exceptions;

use Throwable;

class MissingScopeException extends TokenException
{
    /**
     * The scopes that the user did not have.
     *
     * @var array
     */
    protected array $scopes;

    /**
     * Create a new missing scope exception.
     *
     * @param array|string   $scopes
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(array|string $scopes = [], string $message = 'MissingScopeException', int $code = 43001, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->scopes = is_array($scopes) ? $scopes : [$scopes];
    }

    /**
     * Get the scopes that the user did not have.
     *
     * @return array
     */
    public function scopes(): array
    {
        return $this->scopes;
    }
}
