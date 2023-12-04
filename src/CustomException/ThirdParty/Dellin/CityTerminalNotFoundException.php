<?php

namespace App\CustomException\ThirdParty\Dellin;

use Exception;
use Throwable;

class CityTerminalNotFoundException extends Exception
{
    public function __construct($message = "В вашем городе нет терминалов ТК \"Деловые Линии\"", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}