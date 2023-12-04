<?php

namespace App\CustomException\ThirdParty\Alfabank;

use Exception;
use Throwable;

class PaymentFailedException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}