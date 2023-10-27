<?php

namespace App\Service\ThirdParty\Abcp;

use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AbcpApi
{
    private const DOMAIN = '';

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
}