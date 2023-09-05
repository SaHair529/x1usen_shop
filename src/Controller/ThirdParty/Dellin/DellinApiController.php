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
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
        $response = new JsonResponse();

        try {
            $dellinResponse = $dellinApi->requestCostAndDeliveryTimeCalculator($cartItems, $dataMapping->getData('companyStockAddress'), $requestData);
            $response->setData($dellinResponse->toArray());
        }
        catch (Exception | TransportExceptionInterface $e) {
            if ($e->getResponse()->getStatusCode() === 400) {
                $response->setData([
                    'error_message_for_client' => 'Не удалось провести расчёты, пожалуйста обратитесь к менеджеру'
                ]);
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }

        return $response;
    }
}