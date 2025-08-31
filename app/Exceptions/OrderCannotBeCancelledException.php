<?php

namespace App\Exceptions;

use Exception;

class OrderCannotBeCancelledException extends Exception
{
    public function __construct(string $message = 'This order cannot be cancelled.')
    {
        parent::__construct($message);
    }
}
