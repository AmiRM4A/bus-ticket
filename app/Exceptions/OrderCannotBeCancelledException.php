<?php

namespace App\Exceptions;

use Exception;

class OrderCannotBeCancelledException extends Exception
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? __('api.order_cannot_be_cancelled'));
    }
}
