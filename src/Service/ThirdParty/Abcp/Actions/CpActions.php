<?php

namespace App\Service\ThirdParty\Abcp\Actions;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Класс со всеми методами ABCP API, связанными с контрольной панелью (CP) (Обычно начинающимися с /cp/)
 */
class CpActions
{
    private const CP_FINANCE_PAYMENTS = '/cp/finance/payments';
    private const PAYMENT_ORDER_LINK = '/cp/finance/paymentOrderLink';

    public function __construct(private HttpClientInterface $httpClient, private $domain){}

    /**
     * Добавление оплат
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Admin#.D0.94.D0.BE.D0.B1.D0.B0.D0.B2.D0.BB.D0.B5.D0.BD.D0.B8.D0.B5_.D0.BE.D0.BF.D0.BB.D0.B0.D1.82
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function cpFinancePayments(array $requestBody): ResponseInterface
    {
        $requestBody = array_merge($requestBody, [
            'userlogin' => $_ENV['ABCP_API_LOGIN'],
            'userpsw' => $_ENV['ABCP_API_PASSWORD']
        ]);

        return $this->httpClient->request('POST', $this->domain.self::CP_FINANCE_PAYMENTS, [
            'body' => $requestBody
        ]);
    }

    /**
     * Операция привязки по ранее добавленному платежу
     * Ссылка на метод в документации \/
     * https://www.abcp.ru/wiki/API.ABCP.Admin#.D0.9E.D0.BF.D0.B5.D1.80.D0.B0.D1.86.D0.B8.D1.8F_.D0.BF.D1.80.D0.B8.D0.B2.D1.8F.D0.B7.D0.BA.D0.B8_.D0.BF.D0.BE_.D1.80.D0.B0.D0.BD.D0.B5.D0.B5_.D0.B4.D0.BE.D0.B1.D0.B0.D0.B2.D0.BB.D0.B5.D0.BD.D0.BD.D0.BE.D0.BC.D1.83_.D0.BF.D0.BB.D0.B0.D1.82.D0.B5.D0.B6.D1.83
     * @param array $requestBody
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function paymentOrderLink(array $requestBody): ResponseInterface
    {
        $requestBody = array_merge($requestBody, [
            'userlogin' => $_ENV['ABCP_API_LOGIN'],
            'userpsw' => $_ENV['ABCP_API_PASSWORD']
        ]);

        return $this->httpClient->request('POST', $this->domain.self::PAYMENT_ORDER_LINK, [
            'body' => $requestBody
        ]);
    }
}