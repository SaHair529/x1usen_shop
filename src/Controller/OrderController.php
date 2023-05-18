<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DataMapping;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
class OrderController extends AbstractController
{
    private $statuses;

    public function __construct(DataMapping $dataMapping)
    {
        $this->statuses = $dataMapping->getData('order_statuses');
    }

    #[Route('/my_orders', name: 'order_my_orders')]
    #[IsGranted('ROLE_USER')]
    public function index()
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('order/index.html.twig', [
            'orders' => $user->getOrders()->getIterator(),
            'statuses' => $this->statuses
        ]);
    }
}
