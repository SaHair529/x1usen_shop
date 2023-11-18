<?php

namespace App\Controller;

use App\Service\ThirdParty\Abcp\AbcpApi;
use App\ControllerHelper\CartController\ResponseCreator;
use App\Entity\Order;
use App\Entity\User;
use App\Form\CreateOrderFormType;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use Exception;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route('/cart')]
class CartController extends AbstractController
{
    /**
     * @param Request $req
     * @param AbcpApi $abcpApi
     * @return Response
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/items', name: 'cart_items')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $req, AbcpApi $abcpApi): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $positionIds = array_filter(explode(' ', $req->request->get('cart_items_ids')));

        $order = new Order();
        $orderForm = $this->createForm(CreateOrderFormType::class, $order);
        $orderForm->handleRequest($req);

        if ($orderForm->isSubmitted() && count($positionIds) > 0) {
            /** @var User $user */
            $user = $this->getUser();
            $shipmentAddressId = 0;
            if ($order->getAddress() !== null) {
                $shipmentAddresses = $abcpApi->basketProcessor->getShipmentAddresses($user);

                foreach ($shipmentAddresses as $address) {
                    if ($order->getAddress() === $address['name'])
                        $shipmentAddressId = $address['id'];
                }
            }

            $abcpCreateOrderResponse = $abcpApi->basketProcessor->createOrder($user, $positionIds, $shipmentAddressId);
            $order = current($abcpCreateOrderResponse->toArray(false)['orders']);

            return $this->render('order/show.html.twig', [
                'order' => $order
            ]);

            # объявление полей в $order из формы

            # указание статуса $order
            # указание клиента $order
            # Добавление товаров из корзины в $order

            # Отмечаем товар корзины, как уже заказанный (т.е. скрытый из корзины)
            # отправка эл. почты если указан email

            # Отправка заказа в деловые линии, если доставка по РФ
            #_____________________________________________________

            # Оплата в альфабанке, если тип оплаты через сайт
        }
        elseif ($orderForm->isSubmitted() && count($positionIds) <= 0) {
            $this->addFlash('danger', 'Выберите товар в корзине (поставьте галочку слева от товара)');
        }

        $cartItems = $abcpApi->basketProcessor->getBasketArticles($user);
        foreach ($cartItems as $key => $cartItem) {
            $cartItems[$key]['articleItem'] = $abcpApi->searchProcessor->getConcreteArticleByItemKeyAndNumber($cartItem['itemKey'], $cartItem['number']);
        }

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems,
            'order_form' => $orderForm,
            'YANDEX_GEOCODER_API_KEY' => $_ENV['YANDEX_GEOCODER_API_KEY'],
            'YANDEX_SUGGEST_API_KEY' => $_ENV['YANDEX_SUGGEST_API_KEY']
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/decrease_quantity', name: 'cart_decrease_quantity')]
    public function decreaseQuantity(Request $req, CartItemRepository $cartItemRep, ProductRepository $productRep, AbcpApi $abcpApi): Response
    {
        /** @var User $user */
        if (is_null($user = $this->getUser()))
            return ResponseCreator::notAuthorized();

        $abcpArticleItem = json_decode($req->getContent(), true);
        $userBasketArticle = $abcpApi->basketProcessor->getArticleFromBasket($user, $abcpArticleItem['itemKey']);

        if (isset($userBasketArticle['itemKey'])) {
            $userBasketArticle['quantity']--;
            $abcpApi->basketProcessor->setArticleQuantity($userBasketArticle['quantity'], $abcpArticleItem, $user);

            return ResponseCreator::decreaseQuantity_ok($userBasketArticle['quantity'], $abcpArticleItem['price'], $abcpArticleItem['availability']);
        }

        return ResponseCreator::decreaseQuantity_cartItemNotFound();
    }

    /**
     * @param Request $req
     * @param ProductRepository $productRep
     * @param CartItemRepository $cartItemRep
     * @param AbcpApi $abcpApi
     * @return Response
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/add_item', name: 'cart_add_item', methods: 'POST')]
    public function addItem(Request $req, AbcpApi $abcpApi): Response
    {
        /** @var User $user */
        if (is_null($user = $this->getUser()))
            return ResponseCreator::notAuthorized();

        $abcpArticleItem = json_decode($req->getContent(), true);
        $userBasketArticle = $abcpApi->basketProcessor->getArticleFromBasket($user, $abcpArticleItem['itemKey']);

        if ($abcpArticleItem['availability'] <= $userBasketArticle['quantity'])
            return ResponseCreator::outOfStock();

        $abcpApi->basketProcessor->addArticleToBasket($user, $abcpArticleItem);
        $userBasketArticle['quantity']++;

        return ResponseCreator::addItem_ok($userBasketArticle, $abcpArticleItem);
    }

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

        return ResponseCreator::ok();
    }

    /**
     * Получение CartItem продукта
     * @param Request $req
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/get_product_cart_item', name: 'cart_get_product_cart_item')]
    public function getProductRelatedCartItem(Request $req): JsonResponse
    {
        if (is_null($this->getUser()))
            return ResponseCreator::notAuthorized();

        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();
        $serializerContext = SerializationContext::create();
        $serializerContext->setSerializeNull(true);
        foreach ($cartItems->getIterator() as $item) {
            if(!$item->isInOrder() && $item->getProduct()->getId() === (int) $req->get('product_id')) {
                return ResponseCreator::getProductRelatedCartItem_ok($item);
            }
        }

        return ResponseCreator::getProductRelatedCartItem_cartItemNotFound();
    }
}
