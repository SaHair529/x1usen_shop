<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\AbcpApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/item/{id}', name: 'order_page')]
    #[IsGranted('ROLE_USER')]
    public function show($id, AbcpApi $abcpApi): Response
    {
        if (!is_numeric($id))
            return $this->redirectToRoute('homepage');

        /** @var User $user */
        $user = $this->getUser();
        $order = $abcpApi->basketProcessor->getOrderByNumber($user, $id);

        return $this->render('order/show.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/my_orders', name: 'order_my_orders')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $req, AbcpApi $abcpApi): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $ordersSkip = $req->get('ordersSkip', 0);
        $ordersLimit = $req->get('ordersLimit', 100);

        $orders = $abcpApi->orderProcessor->getUserOrders($user, $ordersSkip, $ordersLimit)['items'];
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
}
