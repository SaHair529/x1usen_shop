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
    private const ADD_PERMISSIONS_URL = 'https://cp.abcp.ru/ajaxRoute/customers/ApiPermissions/update';

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

    public static function addAllPermissions(string $customerId, string $cookies)
    {
        $httpClient = HttpClient::create();

        $requestData = [
            'customerId' => $customerId,
            'permissions[1]' => 1,
            'permissions[2]' => 1,
            'permissions[4]' => 1,
            'permissions[5]' => 1,
            'permissions[6]' => 1,
            'permissions[16]' => 1,
            'permissions[25]' => 1,
            'permissions[26]' => 1,
            'permissions[27]' => 1,
            'permissions[28]' => 1,
            'permissions[29]' => 1,
            'permissions[32]' => 1,
            'permissions[1007]' => 1,
            'checkWoPrice' => 0,
            'checkWoAvailability' => 0,
            'allowOnlineRequest' => 1
        ];

        $headers = [
            'Cookie' => $cookies
        ];

        $httpClient->request('POST', self::ADD_PERMISSIONS_URL, [
            'body' => $requestData,
            'headers' => $headers
        ]);
    }
}