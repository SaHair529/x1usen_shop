<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Service\DataMapping;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
class OrderController extends AbstractController
{
    private array $statuses;
    private array $waysToGet;

    #[Pure]
    public function __construct(DataMapping $dataMapping)
    {
        $this->statuses = $dataMapping->getData('order_statuses');
        $this->waysToGet = $dataMapping->getData('order_ways_to_get');
    }

    #[Route('/my_orders', name: 'order_my_orders')]
    #[IsGranted('ROLE_USER')]
    public function index(OrderRepository $orderRep): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $orders = iterator_to_array($user->getOrders()->getIterator());
        usort($orders, function ($o1, $o2) {
            $hasNotifications1 = count($o1->getNotifications()) > 0;
            $hasNotifications2 = count($o2->getNotifications()) > 0;
            if ($hasNotifications1 && !$hasNotifications2)
                return -1;
            elseif (!$hasNotifications1 && $hasNotifications2)
                return 1;
            return 0;
        });

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
            'statuses' => $this->statuses,
            'ways_to_get' => $this->waysToGet
        ]);
    }
}
