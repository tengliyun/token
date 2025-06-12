<?php

namespace Tengliyun\Token\Exceptions;

use Throwable;

class InvalidRefreshTokenException extends TokenException
{
    public function __construct(string $message = 'InvalidRefreshTokenException', int $code = 42001, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
