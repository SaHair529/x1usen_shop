<?php

namespace App\Service\ThirdParty\Abcp\Actions;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Класс со всеми методами ABCP API, связанными с поиском товаров (Обычно начинающимися с /search/)
 */
class SearchActions
{
    private const ARTICLES = '/search/articles';
    private const BRANDS = '/search/brands';
    private const BATCH = '/search/batch';

    public function __construct(private HttpClientInterface $httpClient, private $domain){}

    /**
     * Поиск детали по номеру и бренду
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.BE.D0.B8.D1.81.D0.BA_.D0.B4.D0.B5.D1.82.D0.B0.D0.BB.D0.B8_.D0.BF.D0.BE_.D0.BD.D0.BE.D0.BC.D0.B5.D1.80.D1.83_.D0.B8_.D0.B1.D1.80.D0.B5.D0.BD.D0.B4.D1.83
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function articles(array $requestBody): ResponseInterface
    {
        $requestBody = array_merge($requestBody, [
            'userlogin' => $_ENV['ABCP_API_LOGIN'],
            'userpsw' => $_ENV['ABCP_API_PASSWORD']
        ]);

        $queryParams = http_build_query($requestBody);

        return $this->httpClient->request('GET', $this->domain.self::ARTICLES.'?'.$queryParams);
    }

    /**
     * Поиск брендов по номеру
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.BE.D0.B8.D1.81.D0.BA_.D0.B1.D1.80.D0.B5.D0.BD.D0.B4.D0.BE.D0.B2_.D0.BF.D0.BE_.D0.BD.D0.BE.D0.BC.D0.B5.D1.80.D1.83
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function brands(array $requestBody): ResponseInterface
    {
        $requestBody = array_merge($requestBody, [
            'userlogin' => $_ENV['ABCP_API_LOGIN'],
            'userpsw' => $_ENV['ABCP_API_PASSWORD']
        ]);

        $queryParams = http_build_query($requestBody);

        return $this->httpClient->request('GET', $this->domain.self::BRANDS.'?'.$queryParams);
    }

    /**
     * Пакетный запрос без учета аналогов
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Client#.D0.9F.D0.B0.D0.BA.D0.B5.D1.82.D0.BD.D1.8B.D0.B9_.D0.B7.D0.B0.D0.BF.D1.80.D0.BE.D1.81_.D0.B1.D0.B5.D0.B7_.D1.83.D1.87.D0.B5.D1.82.D0.B0_.D0.B0.D0.BD.D0.B0.D0.BB.D0.BE.D0.B3.D0.BE.D0.B2
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function batch(array $requestBody): ResponseInterface
    {
        $requestBody = array_merge($requestBody, [
            'userlogin' => $_ENV['ABCP_API_LOGIN'],
            'userpsw' => $_ENV['ABCP_API_PASSWORD']
        ]);

        $queryParams = http_build_query($requestBody);

        return $this->httpClient->request('GET', $this->domain.self::BATCH.'?'.$queryParams);
    }
}