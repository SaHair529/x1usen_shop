<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Обертка для класса @link BasketActions
 */
class BasketProcessor
{
    public function __construct(private BasketActions $basketActions){}

    /**
     * Добавление товара в корзину ABCP
     * @param User $user
     * @param array $article
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function addArticleToBasket(User $user, array &$article): ResponseInterface
    {
        return $this->basketActions->add([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'positions' => [
                [
                    'brand' => $article['brand'],
                    'number' => $article['number'],
                    'itemKey' => $article['itemKey'],
                    'supplierCode' => $article['supplierCode'],
                    'quantity' => 1
                ]
            ]
        ]);
    }

    /**
     * @param User $user
     * @param string $itemKey
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getArticleFromBasket(User $user, string $itemKey): array
    {
        $userBasketArticles = $this->basketActions->content([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5()
        ])->toArray();

        foreach ($userBasketArticles as $basketArticle) {
            if ($basketArticle['itemKey'] === $itemKey)
                return $basketArticle;
        }

        return [
            'quantity' => 0
        ];
    }

    public function getBasketArticles(User $user): array
    {
        return $this->basketActions->content([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5()
        ])->toArray(false);
    }
}