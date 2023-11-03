<?php

namespace App\Service\ThirdParty\Abcp;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use App\Service\ThirdParty\Abcp\Actions\SearchActions;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AbcpApi
{
    private const DOMAIN = 'https://abcp59784.public.api.abcp.ru';

    private HttpClientInterface $httpClient;

    public BasketActions $basketActions;
    public OrderActions $orderActions;
    public UserActions $userActions;
    public SearchActions $searchActions;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();

        $this->basketActions = new BasketActions($this->httpClient, self::DOMAIN);
        $this->orderActions = new OrderActions($this->httpClient, self::DOMAIN);
        $this->userActions = new UserActions($this->httpClient, self::DOMAIN);
        $this->searchActions = new SearchActions($this->httpClient, self::DOMAIN);
    }

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

    /**
     * Регистрация нового пользователя в ABCP и его активация
     * @param User $user
     * @param FormInterface $form
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function registerUser(User $user, FormInterface $form): ResponseInterface
    {
        $userFullnameArray = explode(' ', $user->getName());

        $registerUserRequestData = [
            'name' => $userFullnameArray[0],
            'password' => $form->get('plainPassword')->getData(),
            'mobile' => $user->getPhone()
        ];

        if (isset($userFullnameArray[1]))
            $registerUserRequestData['surname'] = $userFullnameArray[1];
        if (isset($userFullnameArray[2]))
            $registerUserRequestData['secondName'] = $userFullnameArray[2];

        return $this->userActions->new($registerUserRequestData);
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