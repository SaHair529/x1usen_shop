<?php

namespace App\Service\ThirdParty\Abcp;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use App\Service\ThirdParty\Abcp\Actions\SearchActions;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use App\Service\ThirdParty\Abcp\Processors\SearchProcessor;
use App\Service\ThirdParty\Abcp\Processors\UserProcessor;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AbcpApi
{
    private const DOMAIN = 'https://abcp59784.public.api.abcp.ru';

    private HttpClientInterface $httpClient;

    public SearchProcessor $searchProcessor;
    public UserProcessor $userProcessor;

    public BasketActions $basketActions;
    public OrderActions $orderActions;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();

        $this->basketActions = new BasketActions($this->httpClient, self::DOMAIN); # todo добавить BasketProcessor
        $this->orderActions = new OrderActions($this->httpClient, self::DOMAIN); # todo добавить OrderProcessor

        $this->searchProcessor = new SearchProcessor(new SearchActions($this->httpClient, self::DOMAIN));
        $this->userProcessor = new UserProcessor(new UserActions($this->httpClient, self::DOMAIN));
    }

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