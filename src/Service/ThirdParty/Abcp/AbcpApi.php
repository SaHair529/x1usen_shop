<?php

namespace App\Service\ThirdParty\Abcp;

use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use App\Service\ThirdParty\Abcp\Actions\SearchActions;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use App\Service\ThirdParty\Abcp\Processors\BasketProcessor;
use App\Service\ThirdParty\Abcp\Processors\OrderProcessor;
use App\Service\ThirdParty\Abcp\Processors\SearchProcessor;
use App\Service\ThirdParty\Abcp\Processors\UserProcessor;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AbcpApi
{
    private const DOMAIN = 'https://abcp59784.public.api.abcp.ru';

    private HttpClientInterface $httpClient;

    public SearchProcessor $searchProcessor;
    public UserProcessor $userProcessor;
    public BasketProcessor $basketProcessor;
    public OrderProcessor $orderProcessor;


    public function __construct()
    {
        $this->httpClient = HttpClient::create();

        $this->searchProcessor = new SearchProcessor(new SearchActions($this->httpClient, self::DOMAIN));
        $this->userProcessor = new UserProcessor(new UserActions($this->httpClient, self::DOMAIN));
        $this->basketProcessor = new BasketProcessor(new BasketActions($this->httpClient, self::DOMAIN));
        $this->orderProcessor = new OrderProcessor(new OrderActions($this->httpClient, self::DOMAIN));
    }
}