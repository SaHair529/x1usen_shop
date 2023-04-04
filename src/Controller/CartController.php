<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\CartItemRepository;
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
        $cartItems = $user->getCart()->getItems();

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems->getIterator()
        ]);
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    #[Route('/remove_item', name: 'cart_remove_item', methods: 'GET')]
    public function removeItem(Request $req, CartItemRepository $cartItemRep, ProductRepository $productRep): Response
    {
        $itemId = $req->query->get('item_id');
        if (!is_null($itemId) && !is_null($item = $cartItemRep->findOneBy(['id'=>$itemId]))) {
            $itemProduct = $item->getProduct();
            $itemProduct->incrementTotalBalance();
            $productRep->save($itemProduct);
            $cartItemRep->remove($item, true);
        }

        return $this->redirectToRoute('cart_items');
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    #[Route('/add_item', name: 'cart_add_item', methods: 'GET')]
    public function addItem(Request $req, ProductRepository $productRep, CartRepository $cartRep, CartItemRepository $cartItemRep): Response
    {
        if (is_null($this->getUser())) {
            return (new Response('not authorized'))
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $productId = $req->query->get('item_id');
        if (!is_null($productId) && !is_null($product = $productRep->findOneBy(['id'=>$productId]))) {
            if ($product->getTotalBalance() <= 0)
                return new Response('out of stock');

            $product->decrementTotalBalance();
            $productRep->save($product);

            /** @var Cart $cart */
            $cart = $this->getUser()->getCart();
            $cartItem = new CartItem($product, 1);
            $cart->addItem($cartItem);
            $cartItemRep->save($cartItem, true);
        }
        return new Response('ok');
    }
}
