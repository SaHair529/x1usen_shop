<?php

namespace App\Controller\ThirdParty\Dellin;

use App\Entity\CartItem;
use App\Entity\User;
use App\Service\DataMapping;
use App\Service\ThirdParty\Dellin\DellinApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/thirdparty/dellin')]
class DellinApiController extends AbstractController
{
    #[Route('/calculate_cost_and_delivery_time', name: 'dellin_calculate_cost_and_delivery_time')]
    #[IsGranted('ROLE_USER')]
    public function calculateCostAndDeliveryTime(Request $req, DellinApi $dellinApi, DataMapping $dataMapping): JsonResponse
    {
        $requestData = json_decode($req->getContent(), true);

        $user = /** @var User $user */ $this->getUser();
        $cartItems = $user->getCart()->getItems();

        $produceDate = '2023-08-30'; # todo
        $cargoMaxLength = 0.42; # todo
        $cargoMaxWidth = 0.18; # todo
        $cargoMaxHeight = 0.3; # todo
        $cargoWeight = 25.0; # todo
        $cargoTotalWeight = 0.02; # todo
        $cargoTotalVolume = 0.02; # todo
        $derivalWorktimeStart = '12:00'; # todo
        $derivalWorktimeEnd = '21:00'; # todo
        $arrivalWorktimeStart = '16:00'; # todo
        $arrivalWorktimeEnd = '16:30'; # todo

        /** @var CartItem $cartItem */
        foreach ($cartItems as $cartItem) {
            if (str_contains($requestData['checkedCartItemsIds'], $cartItem->getId())) {
                if ($cargoMaxLength < $productLength = $cartItem->getProduct()->getLength())
                    $cargoMaxLength = $productLength;
                if ($cargoMaxWidth < $productWidth = $cartItem->getProduct()->getWidth())
                    $cargoMaxWidth = $productWidth;
                if ($cargoMaxHeight < $productHeight = $cartItem->getProduct()->getHeight())
                    $cargoMaxHeight = $productHeight;
                if ($cargoWeight < $productWeight = $cartItem->getProduct()->getWeight())
                    $cargoWeight = $productWeight;
                $cargoTotalWeight += $cartItem->getProduct()->getWeight();
                $cargoTotalVolume +=
                    $cartItem->getProduct()->getLength() *
                    $cartItem->getProduct()->getWidth() *
                    $cartItem->getProduct()->getHeight();
            }
        }

        $dellinResponse = $dellinApi->requestCostAndDeliveryTimeCalculator(
            $produceDate,
            $dataMapping->getData('companyStockAddress'),
            "{$requestData['city']}, {$requestData['address']}",
            $cargoMaxLength,
            $cargoMaxWidth,
            $cargoMaxHeight,
            $cargoWeight,
            $cargoTotalWeight,
            $cargoTotalVolume,
            $derivalWorktimeStart,
            $derivalWorktimeEnd,
            $arrivalWorktimeStart,
            $arrivalWorktimeEnd
        );

        $dellinResponseData = $dellinResponse->toArray();

        return new JsonResponse($dellinResponseData);
    }
}