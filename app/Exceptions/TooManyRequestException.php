<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class TooManyRequestException extends Exception
{
    public function __construct($message = "", $code = 429, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
