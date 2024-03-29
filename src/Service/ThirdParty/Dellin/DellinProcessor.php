<?php

namespace App\Service\ThirdParty\Dellin;

use App\CustomException\ThirdParty\Dellin\CityTerminalNotFoundException;
use App\Entity\Order;
use App\Entity\User;
use App\Service\DataMapping;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class DellinProcessor
{
    public function __construct(private DellinApi $dellinApi)
    {
    }

    /**
     * @throws TransportExceptionInterface|CityTerminalNotFoundException
     */
    public function requestTransportation(array $abcpOrderPositions, User $user, Order $orderEntity)
    {
        $dataMapping = new DataMapping();

        $derivalAddress = $dataMapping->getData('companyStockAddress');
        $companyOwnerFullname = $dataMapping->getData('companyOwnerFullname');
        $companyContactPhone = $dataMapping->getData('companyContactPhone');
        $companyINN = $dataMapping->getData('companyINN');

        $this->dellinApi->requestConsolidatedCargoTransportation(
            $derivalAddress,
            $companyOwnerFullname, $companyINN, $companyContactPhone,
            $abcpOrderPositions, $orderEntity, $user
        );
    }
}