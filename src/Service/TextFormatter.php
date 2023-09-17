<?php

namespace App\Service;

use Exception;

class TextFormatter
{
    /**
     * Форматирование номера телефона под правила API Деловых линий (7**********)
     * 79992424328  -> 79992424328
     * 9992424328   -> 79992424328
     * 89992424328  -> 79992424328
     * @throws Exception
     */
    public static function reformatPhoneForDellinRequest(string $phone): string | null
    {
        $formattedPhone = preg_replace("/[^0-9]/", "", $phone);

        if ($formattedPhone[0] === '7' && strlen($formattedPhone) === 11)
            return $formattedPhone;

        if ($formattedPhone[0] === '9' && strlen($formattedPhone) === 10)
            return '7'.$formattedPhone;

        if ($formattedPhone[0] === '8' && strlen($formattedPhone) === 11)
            return '7'.substr($formattedPhone, 1);

        throw new Exception('Некорректный номер телефона');
    }
}