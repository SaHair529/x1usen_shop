<?php

namespace App\Service\ThirdParty\Dellin;

use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TraceableAdapter;
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
    public function __construct(TraceableAdapter $cacheAdapter) {
        $this->client = HttpClient::create();
        $this->memcached = $cacheAdapter->getPool();
        $this->setSessionId();
        $this->dataPreparer = new DellinRequestDataPreparer();
    }

    /**
     * Запрос на перевозку сборных грузов
     * https://dev.dellin.ru/api/ordering/ltl-request/#_header3
     */
    public function requestConsolidatedCargoTransportation(
        string $deliveryProduceDate,
        string $derivalAddress,
        string $arrivalAddress,
        string $cargoMaxLength,
        string $cargoMaxWidth,
        string $cargoMaxHeight,
        string $cargoWeight,
        string $cargoTotalWeight,
        string $cargoTotalVolume,
        string $requesterUID,
        string $senderFullname,
        string $senderINN,
        string $senderContactPersonName,
        string $senderContactPersonPhone,
        string $receiverPhone,
        string $receiverName
    )
    {
        $this->client->request('POST', "{$_ENV['DELLIN_API_DOMAIN']}/v2/request.json",
            $this->dataPreparer->prepareConsolidatedCargoTransportationData(
                $_ENV['DELLIN_APP_KEY'],
                $this->sessionId,
                $deliveryProduceDate,
                $derivalAddress,
                $arrivalAddress,
                $cargoMaxLength,
                $cargoMaxWidth,
                $cargoMaxHeight,
                $cargoWeight,
                $cargoTotalWeight,
                $cargoTotalVolume,
                $requesterUID,
                $senderFullname,
                $senderINN,
                $senderContactPersonName,
                $senderContactPersonPhone,
                $receiverPhone,
                $receiverName
            )
        );
    }

    /**
     * Запрос на калькулятор стоимости и сроков перевозки
     * https://dev.dellin.ru/api/calculation/calculator/#_header14
     */
    public function requestCostAndDeliveryTimeCalculator(string $produceDate,
                                                         string $derivalAddress,
                                                         string $arrivalAddress,
                                                         string $cargoMaxLength,
                                                         string $cargoMaxWidth,
                                                         string $cargoMaxHeight,
                                                         string $cargoWeight,
                                                         string $cargoTotalWeight,
                                                         string $cargoTotalVolume)
    {
        $this->client->request('POST', "{$_ENV['DELLIN_API_DOMAIN']}/v2/calculator.json", [
            'appkey' => $_ENV['DELLIN_APP_KEY'],
            'sessionID' => $this->sessionId,
            'delivery' => [
                'deliveryType' => [
                    'type' => 'auto'
                ],
                'derival' => [
                    'produceDate' => $produceDate,
                    'variant' => 'address',
                    'address' => [
                        'search' => $derivalAddress
                    ],
                    'time' => [ # todo заменить тестовое время на реальное и узнать подробнее об этом параметре
                        'worktimeStart' => '12:00',
                        'worktimeEnd' => '21:00'
                    ]
                ],
                'arrival' => [
                    'variant' => 'address',
                    'address' => [
                        'search' => $arrivalAddress
                    ],
                    'time' => [ # todo заменить тестовое время на реальное и узнать подробнее об этом параметре
                        'worktimeStart' => '16:00',
                        'worktimeEnd' => '16:30'
                    ]
                ]
            ],
            'cargo' => [
                'quantity' => '1',
                'length' => $cargoMaxLength,
                'width' => $cargoMaxWidth,
                'height' => $cargoMaxHeight,
                'weight' => $cargoWeight,
                'totalVolume' => $cargoTotalVolume,
                'totalWeight' => $cargoTotalWeight,
                'freightUID' => '0x982400215e7024d411e1e844ef594aad'
            ]
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
    public function auth(): ResponseInterface
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