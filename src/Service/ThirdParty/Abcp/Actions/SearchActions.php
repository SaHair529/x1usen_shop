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
    const ARTICLES = 'search/articles';

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
        return $this->httpClient->request('GET', $this->domain.self::ARTICLES, [
            'body' => $requestBody
        ]);
    }
}