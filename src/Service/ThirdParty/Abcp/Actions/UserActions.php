<?php

namespace App\Service\ThirdParty\Abcp\Actions;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Класс со всеми методами ABCP API, связанными с пользователями (Обычно начинающимися с /user/)
 */
class UserActions
{
    private const NEW_URL = ''; # todo
    private const ACTIVATION_URL = ''; # todo
    private const INFO_URL = ''; # todo
    private const RESTORE_URL = ''; # todo

    public function __construct(private HttpClientInterface $httpClient){}

    /**
     * Регистрация пользователя
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.A0.D0.B5.D0.B3.D0.B8.D1.81.D1.82.D1.80.D0.B0.D1.86.D0.B8.D1.8F_.D0.BF.D0.BE.D0.BB.D1.8C.D0.B7.D0.BE.D0.B2.D0.B0.D1.82.D0.B5.D0.BB.D1.8F
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function new(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', self::NEW_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Активация пользователя
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.90.D0.BA.D1.82.D0.B8.D0.B2.D0.B0.D1.86.D0.B8.D1.8F_.D0.BF.D0.BE.D0.BB.D1.8C.D0.B7.D0.BE.D0.B2.D0.B0.D1.82.D0.B5.D0.BB.D1.8F
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function activation(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', self::ACTIVATION_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Получение данных пользователя (авторизация)
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.BE.D0.BB.D1.83.D1.87.D0.B5.D0.BD.D0.B8.D0.B5_.D0.B4.D0.B0.D0.BD.D0.BD.D1.8B.D1.85_.D0.BF.D0.BE.D0.BB.D1.8C.D0.B7.D0.BE.D0.B2.D0.B0.D1.82.D0.B5.D0.BB.D1.8F_.28.D0.B0.D0.B2.D1.82.D0.BE.D1.80.D0.B8.D0.B7.D0.B0.D1.86.D0.B8.D1.8F.29
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function info(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', self::INFO_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Восстановление пароля
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.92.D0.BE.D1.81.D1.81.D1.82.D0.B0.D0.BD.D0.BE.D0.B2.D0.BB.D0.B5.D0.BD.D0.B8.D0.B5_.D0.BF.D0.B0.D1.80.D0.BE.D0.BB.D1.8F
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function restore(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', self::RESTORE_URL, [
            'body' => $requestBody
        ]);
    }
}