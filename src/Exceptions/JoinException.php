<?php

namespace Aberdeener\Koss\Exceptions;

use Exception;

final class JoinException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
