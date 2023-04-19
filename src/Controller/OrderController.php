<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\CreateOrderFormType;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/my_orders', name: 'order_my_orders')]
    #[IsGranted('ROLE_USER')]
    public function index()
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('order/index.html.twig', [
            'orders' => $user->getOrders()->getIterator()
        ]);
    }

    #[Route('/create', name: 'order_create')]
    public function createOrder(Request $request, OrderRepository $orderRep): Response
    {
        $order = new Order();
        $form = $this->createForm(CreateOrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var User $user */
            $user = $this->getUser();

            $order->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));
            $order->setPhoneNumber($form->get('phone_number')->getData());
            $order->setCity($form->get('city')->getData());
            $order->setAddress($form->get('address')->getData());
            $order->setCustomer($user);

            # Добавление товаров из корзины
            $cartItems = $user->getCart()->getItems();
            foreach ($cartItems->getIterator() as $item) {
                $order->addItem($item);
            }

            $orderRep->save($order, true);
        }

        return $this->render('order/create.html.twig', [
            'form' => $form,
        ]);
    }
}
