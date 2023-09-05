<?php

namespace App\Service\ThirdParty\Alfabank;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AlfabankApi
{
    private HttpClientInterface $httpClient;
    private const REGISTER_ORDER_URL = 'https://alfa.rbsuat.com/payment/rest/register.do';

    public function __construct() {
        $this->httpClient = HttpClient::create();
    }

    /**
     * Запрос на создание нового заказа в альфабанке
     * Ссылка на страницу с информацией о запросе в документации:
     * https://pay.alfabank.ru/ecommerce/instructions/merchantManual/pages/index/rest.html#zapros_registratsii_zakaza_rest_
     *
     * @param float $costInCopecks  - Сумма оплаты заказа в копейках, которую должен заплатить клиент
     * @param string $returnUrl     - Url страницы, на которую альфабанк перебросит после успешной оплаты
     * @param string $failUrl       - Url страницы, на которую альфабанк перебросит после безуспешной оплаты
     * @return ResponseInterface    - Помимо всего прочего, возвращает url страницы оплаты созданного заказа
     * @throws TransportExceptionInterface
     */
    public function registerOrder(float $costInCopecks, string $returnUrl, string $failUrl): ResponseInterface
    {
        $requestData = [
            'userName'  => $_ENV['ALFABANK_USERNAME'],
            'password'  => $_ENV['ALFABANK_PASSWORD'],
            'token'     => $_ENV['ALFABANK_TOKEN'],
            'amount'    => $costInCopecks,
            'returnUrl' => $returnUrl,
            'failUrl'   => $failUrl
        ];

        return $this->httpClient->request('POST', self::REGISTER_ORDER_URL, [
            'json' => $requestData
        ]);
    }
}