<?php

namespace App\Service\ThirdParty\Abcp\Actions;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Класс со всеми методами ABCP API, связанными с корзиной (Обычно начинающимися с /basket/)
 */
class BasketActions
{
    private const ADD_URL = ''; # todo
    private const CLEAR_URL = ''; # todo
    private const CONTENT_URL = ''; # todo
    private const ORDER_URL = ''; # todo

    public function __construct(private HttpClientInterface $httpClient, private $domain){}

    /**
     * Добавление товаров в корзину. Удаление товара из корзины
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.94.D0.BE.D0.B1.D0.B0.D0.B2.D0.BB.D0.B5.D0.BD.D0.B8.D0.B5_.D1.82.D0.BE.D0.B2.D0.B0.D1.80.D0.BE.D0.B2_.D0.B2_.D0.BA.D0.BE.D1.80.D0.B7.D0.B8.D0.BD.D1.83._.D0.A3.D0.B4.D0.B0.D0.BB.D0.B5.D0.BD.D0.B8.D0.B5_.D1.82.D0.BE.D0.B2.D0.B0.D1.80.D0.B0_.D0.B8.D0.B7_.D0.BA.D0.BE.D1.80.D0.B7.D0.B8.D0.BD.D1.8B
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function add(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('POST', $this->domain.self::ADD_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Очистка корзины
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9E.D1.87.D0.B8.D1.81.D1.82.D0.BA.D0.B0_.D0.BA.D0.BE.D1.80.D0.B7.D0.B8.D0.BD.D1.8B
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function clear(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('POST', $this->domain.self::CLEAR_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Получение списка товаров в корзине
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.BE.D0.BB.D1.83.D1.87.D0.B5.D0.BD.D0.B8.D0.B5_.D1.81.D0.BF.D0.B8.D1.81.D0.BA.D0.B0_.D1.82.D0.BE.D0.B2.D0.B0.D1.80.D0.BE.D0.B2_.D0.B2_.D0.BA.D0.BE.D1.80.D0.B7.D0.B8.D0.BD.D0.B5
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function content(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('GET', $this->domain.self::CONTENT_URL, [
            'body' => $requestBody
        ]);
    }

    /**
     * Отправка корзины в заказ
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9E.D1.82.D0.BF.D1.80.D0.B0.D0.B2.D0.BA.D0.B0_.D0.BA.D0.BE.D1.80.D0.B7.D0.B8.D0.BD.D1.8B_.D0.B2_.D0.B7.D0.B0.D0.BA.D0.B0.D0.B7
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function order(array $requestBody): ResponseInterface
    {
        return $this->httpClient->request('POST', $this->domain.self::ORDER_URL, [
            'body' => $requestBody
        ]);
    }
}