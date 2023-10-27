<?php

namespace App\Service\ThirdParty\Abcp\Actions;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Класс со всеми методами ABCP API, связанными с заказами (Обычно начинающимися с /orders/)
 */
class OrderActions
{
    private const ORDERS_URL = ''; # todo
    private const LIST_URL = ''; # todo

    public function __construct(private HttpClientInterface $httpClient, private $domain){}

    /**
     * Получение списка заказов
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.BE.D0.BB.D1.83.D1.87.D0.B5.D0.BD.D0.B8.D0.B5_.D1.81.D0.BF.D0.B8.D1.81.D0.BA.D0.B0_.D0.B7.D0.B0.D0.BA.D0.B0.D0.B7.D0.BE.D0.B2
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function orders(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', $this->domain.self::ORDERS_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Получение позиций заказов со статусами
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.BE.D0.BB.D1.83.D1.87.D0.B5.D0.BD.D0.B8.D0.B5_.D0.BF.D0.BE.D0.B7.D0.B8.D1.86.D0.B8.D0.B9_.D0.B7.D0.B0.D0.BA.D0.B0.D0.B7.D0.BE.D0.B2_.D1.81.D0.BE_.D1.81.D1.82.D0.B0.D1.82.D1.83.D1.81.D0.B0.D0.BC.D0.B8
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function list(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', $this->domain.self::LIST_URL, [
            'body' => $requestBody
        ]);
    }
}