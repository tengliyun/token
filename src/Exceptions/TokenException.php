<?php

namespace Tengliyun\Token\Exceptions;

use Exception;
use Throwable;

class TokenException extends Exception
{
    public function __construct(string $message = 'TokenException', int $code = 40001, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
