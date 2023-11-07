<?php

namespace App\Controller;

use App\Service\ThirdParty\Abcp\AbcpApi;
use App\Service\ThirdParty\Alfabank\AlfabankApi;
use App\Service\ThirdParty\Dellin\DellinApi;
use App\Service\ThirdParty\Google\EmailSender;
use App\ControllerHelper\CartController\ResponseCreator;
use App\Entity\CartItem;
use App\Entity\Order;
use App\Entity\User;
use App\Form\CreateOrderFormType;
use App\Repository\CartItemRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\DataMapping;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @param CartItemRepository $cartItemRep
     * @param OrderRepository $orderRep
     * @param DataMapping $dataMapping
     * @param EmailSender $emailSender
     * @param UrlGeneratorInterface $urlGenerator
     * @param AlfabankApi $alfabankApi
     * @param DellinApi $dellinApi
     * @return Response
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    #[Route('/items', name: 'cart_items')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $req, CartItemRepository $cartItemRep, OrderRepository $orderRep, DataMapping $dataMapping, EmailSender $emailSender, UrlGeneratorInterface $urlGenerator, AlfabankApi $alfabankApi, DellinApi $dellinApi): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cartItemsIds = array_filter(explode(' ', $req->request->get('cart_items_ids')));

        $order = new Order();
        $orderForm = $this->createForm(CreateOrderFormType::class, $order);
        $orderForm->handleRequest($req);

        if ($orderForm->isSubmitted() && count($cartItemsIds) > 0) {
            /** @var User $user */
            $user = $this->getUser();
            $order->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));
            $order->setClientFullname($orderForm->get('client_fullname')->getData());
            $order->setPhoneNumber($orderForm->get('phone_number')->getData());
            $order->setPaymentType($orderForm->get('payment_type')->getData());
            $order->setWayToGet($orderForm->get('way_to_get')->getData());
            $order->setAddressGeocoords($orderForm->get('addressGeocoords')->getData());
            $order->setPaymentStatus(0); /** @link DataMapping::$order_payment_statuses */

            if (($email = $orderForm->get('email')->getData()) !== null)
                $order->setEmail($email);
            if (($city = $orderForm->get('city')->getData()) !== null)
                $order->setCity($city);
            if (($address = $orderForm->get('address')->getData()) !== null)
                $order->setAddress($address);

            $orderStatuses = $dataMapping->getData('order_statuses');
            if ($order->getPaymentType() === 1)
                $order->setStatus(1);
            else
                $order->setStatus(array_key_first($orderStatuses));
            $order->setCustomer($user);
            # Добавление товаров из корзины
            $cartItems = $cartItemRep->findBy(['id' => $cartItemsIds]);
            $orderTotalPrice = 0;
            foreach ($cartItems as $cartItem) {
                $order->addItem($cartItem);
                $orderTotalPrice += $cartItem->getProduct()->getPrice();
            }
            $orderRep->save($order, true);

            # Отмечаем товар корзины, как уже заказанный (т.е. скрытый из корзины)
            for ($i = 0; $i < count($cartItems); $i++) {
                $cartItems[$i]->setInOrder(true);
                $cartItemRep->save($cartItems[$i], $i+1 === count($cartItems));
            }
            if ($email !== null)
                $emailSender->sendEmailByIGG($email);

            # Отправка заказа в деловые линии, если доставка по РФ
            if ($order->getWayToGet() === 3) {
                $isProductsIndicatedDimensions = true;

                foreach ($cartItems as $item) {
                    if (!$item->getProduct()->getLength() || !$item->getProduct()->getWidth() || !$item->getProduct()->getHeight()) {
                        $isProductsIndicatedDimensions = false;
                        break;
                    }
                }

                if ($isProductsIndicatedDimensions) {
                    # Отправка запроса на заказ в ТК "Деловые линии"
                    $derivalAddress = $dataMapping->getData('companyStockAddress');
                    $companyOwnerFullname = $dataMapping->getData('companyOwnerFullname');
                    $companyContactPhone = $dataMapping->getData('companyContactPhone');
                    $companyINN = $dataMapping->getData('companyINN');

                    $dellinApi->requestConsolidatedCargoTransportation(
                        $derivalAddress,
                        $companyOwnerFullname, $companyINN, $companyContactPhone,
                        $cartItems, $order
                    );
                }
                else {
                    # Установка статуса "Требуется заказ вручную" для заказа
                    $order->setStatus(7);
                    $orderRep->save($order, true);
                }
            }
            #_____________________________________________________

            if ($order->getPaymentType() === 1) { # Если тип оплаты - карточкой через сайт
                $host = Request::createFromGlobals();
                $domain = $host->getScheme().'://'.$host->getHost().':'.$host->getPort();

                $costInCopecks = $orderTotalPrice*100;
                $successPaymentUrl = $domain.$urlGenerator->generate('order_page', ['id' => $order->getId(), 'payment_result' => 'success']);
                $failedPaymentUrl = $domain.$urlGenerator->generate('order_page', ['id' => $order->getId(), 'payment_result' => 'fail']);

                $alfabankResponse = $alfabankApi->registerOrder($costInCopecks, $successPaymentUrl, $failedPaymentUrl, $order->getId());
                $alfabankResponseData = $alfabankResponse->toArray(false);

                $order->setAlfabankOrderId($alfabankResponseData['orderId']);
                $order->setAlfabankPaymentUrl($alfabankResponseData['formUrl']);

                $orderRep->save($order, true);

                return $this->redirect($alfabankResponseData['formUrl']);
            }

            return $this->redirectToRoute('order_page', [
                'id' => $order->getId()
            ]);
        }
        elseif ($orderForm->isSubmitted() && count($cartItemsIds) <= 0) {
            $this->addFlash('danger', 'Выберите товар в корзине (поставьте галочку слева от товара)');
        }

        $cartItems = [];
        $allCartItems = $user->getCart()->getItems()->getIterator();
        foreach ($allCartItems as $item) {
            if (!$item->isInOrder())
            $cartItems[] = $item;
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

            return ResponseCreator::decreaseQuantity_ok($userBasketArticle['quantity'], $abcpArticleItem['price'], $abcpArticleItem['availability']-1);
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
    public function addItem(Request $req, ProductRepository $productRep, CartItemRepository $cartItemRep, AbcpApi $abcpApi): Response
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
