<?php

namespace App\Service\ThirdParty\Dellin;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DellinApi
{
    private const MC_SESSION_ID_KEY = 'dellin_session_id';
    private HttpClientInterface $client;
    private AdapterInterface $memcached;
    private string $sessionId;
    private DellinRequestDataPreparer $dataPreparer;

    #[NoReturn]
    public function __construct(MemcachedAdapter $cacheAdapter) {
        $this->client = HttpClient::create();
        $this->memcached = $cacheAdapter;
        $this->setSessionId();
        $this->dataPreparer = new DellinRequestDataPreparer();
    }

    /**
     * Запрос на перевозку сборных грузов
     * https://dev.dellin.ru/api/ordering/ltl-request/#_header3
     */
    public function requestConsolidatedCargoTransportation(
        string $derivalAddress, string $arrivalAddress,
        string $companyOwnerFullname, string $companyINN, string $companyContactPhone,
        string $receiverPhone, string $receiverName
    )
    {
        $this->client->request('POST', "{$_ENV['DELLIN_API_DOMAIN']}/v2/request.json",
            $this->dataPreparer->prepareConsolidatedCargoTransportationRequestData(
                $this->sessionId,
                $derivalAddress, $arrivalAddress,
                $companyOwnerFullname, $companyINN, $companyContactPhone,
                $receiverPhone, $receiverName
            )
        );
    }

    /**
     * Запрос на калькулятор стоимости и сроков перевозки
     * https://dev.dellin.ru/api/calculation/calculator/#_header14
     * @throws TransportExceptionInterface
     */
    public function requestCostAndDeliveryTimeCalculator($cartItems, string $derivalAddress, array $requestData): ResponseInterface
    {
        $requestData = $this->dataPreparer->prepareCostAndDeliveryTimeCalculatorData($this->sessionId, $cartItems, $derivalAddress, $requestData);

        return $this->client->request('POST', "{$_ENV['DELLIN_API_DOMAIN']}/v2/calculator.json", [
            'json' => $requestData
        ]);
    }





    private function setSessionId(): void
    {
        $cachedSessionId = $this->memcached->getItem(self::MC_SESSION_ID_KEY);

        if ($cachedSessionId->isHit())
            $this->sessionId = $cachedSessionId->get();
        else {
            $this->sessionId = $this->auth()->toArray()['data']['sessionID'];
            $this->cacheSessionId($this->sessionId);
        }
    }

    /**
     * Возвращает данные о сессии, если статус 200
     * @throws TransportExceptionInterface
     */
    private function auth(): ResponseInterface
    {
        return $this->client->request('POST', "{$_ENV['DELLIN_API_DOMAIN']}/v3/auth/login.json", [
            'json' => [
                'appkey' => $_ENV['DELLIN_APP_KEY'],
                'login' => $_ENV['DELLIN_LOGIN'],
                'password' => $_ENV['DELLIN_PASSWORD']
            ]
        ]);
    }

    private function cacheSessionId(string $sessionId)
    {
        $cacheItem = $this->memcached->getItem(self::MC_SESSION_ID_KEY);
        $cacheItem->set($sessionId);
//        $cacheItem->expiresAfter(new DateInterval('PT23H50M')); # todo Указать время жизни sessionId в кэше
        $this->memcached->save($cacheItem);
    }
}