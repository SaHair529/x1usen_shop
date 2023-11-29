<?php

namespace App\Service\ThirdParty\Dellin;

use App\Entity\Order;
use App\Entity\User;
use App\Service\DataMapping;

class DellinProcessor
{
    public function __construct(private DellinApi $dellinApi)
    {
    }

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