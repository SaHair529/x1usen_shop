<?php

namespace App\Service\ThirdParty\Abcp;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use App\Service\ThirdParty\Abcp\Actions\SearchActions;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\HttpClient;
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
     */
    public function addArticleToBasket(User $user, string $productArticleNumber, string $productBrand)
    {
        $foundArticle = $this->searchActions->articles([
            'userlogin' => $user->getUsername(),
            'userpsw' => $user->getPassword(),
            'number' => $productArticleNumber,
            'brand' => $productBrand
        ])->toArray(false);

        $this->basketActions->add([
            'positions' => [
                [
                    'brand' => $productBrand,
                    'number' => $productArticleNumber,
                    'itemKey' => $foundArticle[0]['itemKey'],
                    'quantity' => 1
                ]
            ]
        ]);
    }
}