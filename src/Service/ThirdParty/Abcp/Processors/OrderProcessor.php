<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\OrderActions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OrderProcessor
{
    public function __construct(private OrderActions $orderActions)
    {
    }

    /**
     * @param User $user
     * @param int $skip
     * @param int $limit
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getUserOrders(User $user, $skip = 0, $limit = 100): array
    {
        return $this->orderActions->orders([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'skip' => $skip,
            'limit' => $limit
        ])->toArray(false);
    }

    /**
     * @param User $user
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getUserOrdersWithPositions(User $user, $skip = 0, $limit = 100): array
    {
        $orders = $this->getUserOrders($user, $skip, $limit)['items'];

        return $this->orderActions->list([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'orders' => array_keys($orders)
        ])->toArray(false);
    }
}