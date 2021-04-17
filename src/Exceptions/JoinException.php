<?php 

namespace Aberdeener\Koss\Exceptions;

use Exception;

class JoinException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
