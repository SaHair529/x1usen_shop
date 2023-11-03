<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
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
    public function addArticleToBasket(User $user, array $article): ResponseInterface
    {
        return $this->basketActions->add([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPassword(),
            'positions' => [
                [
                    'brand' => $article['brand'],
                    'number' => $article['number'],
                    'itemKey' => $article['itemKey'],
                    'quantity' => 1
                ]
            ]
        ]);
    }
}