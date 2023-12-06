<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\CpActions;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Обертка для класса @link CpActions
 */
class CpProcessor
{
    public function __construct(private CpActions $cpActions){}

    /**
     * Фиксирование оплаты и привязка его к заказу
     * @param User $user
     * @param int $paymentTypeId
     * @param int|float $amount
     * @param int $orderNumber
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function commitPaymentToOrder(User $user, int $paymentTypeId, int|float $amount, int $orderNumber): void
    {
        $newPayment = $this->cpActions->cpFinancePayments([
            'payments' => [[
                'userId' => $user->getAbcpUserCode(),
                'createDateTime' => (new DateTime('now', new DateTimeZone('Europe/Moscow')))->format('Y-m-d H:i:s'),
                'paymentTypeId' => $paymentTypeId,
                'amount' => $amount
            ]]
        ])->toArray(false)[0];

       $this->cpActions->paymentOrderLink([
            'paymentId' => $newPayment['paymentId'],
            'orderId' => $orderNumber,
            'amount' => $amount
        ])->toArray(false);
    }
}