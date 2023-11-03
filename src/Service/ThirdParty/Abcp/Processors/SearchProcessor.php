<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Service\ThirdParty\Abcp\Actions\SearchActions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Обертка для класса @link SearchActions
 */
class SearchProcessor
{
    public function __construct(private SearchActions $searchActions){}

    /**
     * Поиск товара по Артикулу
     * @param string $number
     * @return array
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function searchArticlesByNumber(string $number): array
    {
        $foundBrand = current($this->searchActions->brands([
            'number' => $number
        ])->toArray(false))['brand'];

        return $this->searchActions->articles([
            'number' => $number,
            'brand' => $foundBrand
        ])->toArray(false);
    }

    /**
     * Поиск конкретного товара по itemKey и артикулу
     * @param string $targetItemKey
     * @param string $articleNumber
     * @return mixed|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getConcreteArticleByItemKeyAndNumber(string $targetItemKey, string $articleNumber): ?array
    {
        $articles = $this->searchArticlesByNumber($articleNumber);

        foreach ($articles as $article) {
            if ($article['itemKey'] === $targetItemKey)
                return $article;
        }

        return null;
    }
}