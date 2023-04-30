<?php

namespace App\Controller;

use App\ControllerHelper\CartController\ResponseCreator;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

#[Route('/cart')]
class CartController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/items', name: 'cart_items')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems->getIterator()
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/decrease_quantity', name: 'cart_decrease_quantity')]
    public function decreaseQuantity(Request $req, CartItemRepository $cartItemRep, ProductRepository $productRep): Response
    {
        if (is_null($this->getUser()))
            return ResponseCreator::notAuthorized();

        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();
        /** @var CartItem $item */
        foreach ($cartItems->getIterator() as $item) {
            if($item->getProduct()->getId() === (int) $req->get('product_id')) {
                $item->decreaseQuantity();
                $item->getProduct()->increaseTotalBalance();
                $item->getQuantity() === 0 ? $cartItemRep->remove($item, true) : $cartItemRep->save($item, true);
                $productRep->save($item->getProduct(), true);
                return ResponseCreator::decreaseQuantity_ok($item->getQuantity(), $item->getProduct()->getPrice());
            }
        }

        return ResponseCreator::decreaseQuantity_cartItemNotFound();
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    #[Route('/add_item', name: 'cart_add_item', methods: 'GET')]
    public function addItem(Request $req, ProductRepository $productRep, CartItemRepository $cartItemRep): Response
    {
        if (is_null($this->getUser()))
            return ResponseCreator::notAuthorized();

        $productId = $req->query->get('item_id');
        $product = $productRep->findOneBy(['id'=>$productId]);
        if (is_null($productId) && is_null($product))
            return ResponseCreator::addItem_productNotFound();

        if ($product->getTotalBalance() <= 0)
            return ResponseCreator::outOfStock();

        /** @var Cart $cart */
        $cart = $this->getUser()->getCart();
        $cartItem = null;
        /** @var CartItem $item */
        foreach ($cart->getItems()->getIterator() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $cartItem = $item;
                break;
            }
        }

        if (is_null($cartItem)) {
            $cartItem = new CartItem($product, 1);
            $cart->addItem($cartItem);
        }
        else
            $cartItem->increaseQuantity();
        $cartItemRep->save($cartItem, true);

        $product->decreaseTotalBalance();
        $productRep->save($product, true);

        return ResponseCreator::addItem_ok($cartItem, $product);
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    #[Route('/remove_item', name: 'cart_remove_item', methods: 'GET')]
    #[IsGranted('ROLE_USER')]
    public function removeItem(Request $req, CartItemRepository $cartItemRep, ProductRepository $productRep): Response
    {
        $itemId = $req->query->get('item_id');
        if (!is_null($itemId) && !is_null($item = $cartItemRep->findOneBy(['id'=>$itemId]))) {
            $itemProduct = $item->getProduct();
            $itemProduct->increaseTotalBalance($item->getQuantity());
            $productRep->save($itemProduct);
            $cartItemRep->remove($item, true);
        }

        return $this->redirectToRoute('cart_items');
    }

    /**
     * Получение CartItem продукта
     * @param Request $req
     * @return JsonResponse
     */
    #[Route('/get_product_cart_item', name: 'cart_get_product_cart_item')]
    public function getProductRelatedCartItem(Request $req, ): JsonResponse
    {
        if (is_null($this->getUser()))
            return ResponseCreator::notAuthorized();

        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();
        $serializerContext = SerializationContext::create();
        $serializerContext->setSerializeNull(true);
        foreach ($cartItems->getIterator() as $item) {
            if($item->getProduct()->getId() === (int) $req->get('product_id')) {
                return ResponseCreator::getProductRelatedCartItem_ok($item);
            }
        }

        return ResponseCreator::getProductRelatedCartItem_cartItemNotFound();
    }
}
