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

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/create', name: 'order_create')]
    public function createOrder(Request $request, OrderRepository $orderRep): Response
    {
        $order = new Order();
        $form = $this->createForm(CreateOrderFormType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $order->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));
            $order->setPhoneNumber($form->get('phone_number')->getData());

            # Добавление товаров из корзины
            /** @var User $user */
            $user = $this->getUser();
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
