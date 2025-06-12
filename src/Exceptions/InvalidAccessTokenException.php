<?php

namespace Tengliyun\Token\Exceptions;

use Throwable;

class InvalidAccessTokenException extends TokenException
{
    public function __construct(string $message = 'InvalidAccessTokenException', int $code = 41001, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
