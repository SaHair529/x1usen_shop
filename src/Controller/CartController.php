<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/items', name: 'cart_items')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getProducts();

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems->getIterator()
        ]);
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    #[Route('/remove_item', name: 'cart_remove_item', methods: 'GET')]
    public function removeItem(Request $req, ProductRepository $productRep, CartRepository $cartRep): Response
    {
        $itemId = $req->query->get('item_id');
        if (!is_null($itemId) && !is_null($item = $productRep->findOneBy(['id'=>$itemId]))) {
            /** @var Cart $cart */
            $cart = $this->getUser()->getCart();
            $cart->removeProduct($item);
            $cartRep->save($cart, true);
        }

        return $this->redirectToRoute('cart_items');
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    #[Route('/add_item', name: 'cart_add_item', methods: 'GET')]
    public function addItem(Request $req, ProductRepository $productRep, CartRepository $cartRep): Response
    {
        if (is_null($this->getUser())) {
            return (new Response('not authorized'))
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $itemId = $req->query->get('item_id');
        if (!is_null($itemId) && !is_null($item = $productRep->findOneBy(['id'=>$itemId]))) {
            /** @var Cart $cart */
            $cart = $this->getUser()->getCart();
            if ($cart->getProducts()->contains($item))
                return new Response('almost in cart');

            $cart->addProduct($item);
            $cartRep->save($cart, true);
        }

        return new Response('ok');
    }
}
