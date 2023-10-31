<?php

namespace App\Service\ThirdParty\Abcp;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AbcpApi
{
    private const DOMAIN = 'https://abcp59784.public.api.abcp.ru';

    private HttpClientInterface $httpClient;

    public BasketActions $basketActions;
    public OrderActions $orderActions;
    public UserActions $userActions;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();

        $this->basketActions = new BasketActions($this->httpClient, self::DOMAIN);
        $this->orderActions = new OrderActions($this->httpClient, self::DOMAIN);
        $this->userActions = new UserActions($this->httpClient, self::DOMAIN);
    }

    /**
     * Регистрация нового пользователя в ABCP и его активация
     * @param User $user
     * @param FormInterface $form
     * @return void
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function registerUser(User $user, FormInterface $form)
    {
        $userFullnameArray = explode(' ', $user->getName());

        $registerUserRequestData = [
            'name' => $userFullnameArray[0],
            'password' => $form->get('plainPassword')->getData()
        ];

        if (isset($userFullnameArray[1]))
            $registerUserRequestData['surname'] = $userFullnameArray[1];
        if (isset($userFullnameArray[2]))
            $registerUserRequestData['secondName'] = $userFullnameArray[2];

        $abcpRegisterResponseData = $this->userActions->new($registerUserRequestData)->toArray(false);
        $this->userActions->activation($abcpRegisterResponseData);
    }
}