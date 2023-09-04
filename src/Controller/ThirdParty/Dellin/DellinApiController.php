<?php

namespace App\Controller\ThirdParty\Dellin;

use App\Entity\CartItem;
use App\Entity\User;
use App\Service\DataMapping;
use App\Service\ThirdParty\Dellin\DellinApi;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $tomorrowDate = (new DateTime())->add(new DateInterval('P1D'));

        $produceDate = $tomorrowDate->format('Y-m-d');
        $cargoMaxLength = 0;
        $cargoMaxWidth = 0;
        $cargoMaxHeight = 0;
        $cargoWeight = 0;
        $cargoTotalWeight = 0;
        $cargoTotalVolume = 0;
        $derivalWorktimeStart = '10:00';
        $derivalWorktimeEnd = '21:00';
        $arrivalWorktimeStart = '10:00'; # todo
        $arrivalWorktimeEnd = '21:00'; # todo

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

        $response = new JsonResponse();

        try {
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
            $response->setData($dellinResponse->toArray());
        }
        catch (Exception $e) {
            if ($e->getResponse()->getStatusCode() === 400) {
                $response->setData([
                    'error_message_for_client' => 'Введите корректный адрес'
                ]);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }

        return $response;
    }
}