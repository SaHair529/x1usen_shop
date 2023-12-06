<?php

namespace App\Service\ThirdParty\Abcp;

use Symfony\Component\HttpClient\HttpClient;

/**
 * Класс для отправки запросов "Из черного хода" в ABCP, которые не доступны по апи.
 * Например, @link AbcpBackDoor::loginToGetAuthToken() входит в систему ABCP для получения authtoken из cookies для
 *  проведения дальнейших операций с использованием authtoken
 */
class AbcpBackDoor
{
    private const LOGIN_URL = 'https://cp.abcp.ru/';
    private const ADD_IP_URL = 'https://cp.abcp.ru/ajaxRoute/customers/addCaIp';

    /**
     * Вход в систему ABCP для получения authtoken из cookies
     */
    public static function loginToGetCookies(string $login, string $pass): string
    {
        $httpClient = HttpClient::create();

        $queryParams = http_build_query([
            'login_start' => 1,
            'login' => $login,
            'pass' => $pass
        ]);

        $headers = $httpClient->request('GET', self::LOGIN_URL.'?'.$queryParams)->getHeaders();
        $responseCookies = $headers['set-cookie'];

        return implode('; ', $responseCookies);
    }

    /**
     * Добавление допустимого ip для пользователя ABCP для работы по API
     */
    public static function addWhiteIPToUser(string $customerId, string $ip, string $cookies)
    {
        $httpClient = HttpClient::create();

        $requestData = [
            'customerId' => $customerId,
            'ip' => $ip
        ];
        $headers = [
            'Cookie' => $cookies
        ];

        $httpClient->request('POST', self::ADD_IP_URL, [
            'body' => $requestData,
            'headers' => $headers
        ]);
    }
}