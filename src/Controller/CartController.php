<?php

namespace App\Controller;

use App\Service\ThirdParty\Alfabank\AlfabankApi;
use App\Service\ThirdParty\Dellin\DellinApi;
use App\Service\ThirdParty\Google\EmailSender;
use App\ControllerHelper\CartController\ResponseCreator;
use App\Entity\Cart;
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

#[Route('/cart')]
class CartController extends AbstractController
{
    /**
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
                        $derivalAddress, $order->getCity().', '.$order->getAddress(),
                        $companyOwnerFullname, $companyINN, $companyContactPhone,
                        $order->getPhoneNumber(), $order->getClientFullname(),
                        $cartItems
                    );
                }
                else {
                    # Установка статуса "Требуется заказ вручную" для заказа
                    $order->setStatus(7);
                    $orderRep->save($order, true);
                }
            }
            #_____________________________________________________

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
    public function decreaseQuantity(Request $req, CartItemRepository $cartItemRep, ProductRepository $productRep): Response
    {
        if (is_null($this->getUser()))
            return ResponseCreator::notAuthorized();

        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();
        /** @var CartItem $item */
        foreach ($cartItems->getIterator() as $item) {
            if(!$item->isInOrder() && $item->getProduct()->getId() === (int) $req->get('product_id')) {
                $item->decreaseQuantity();
                $item->getProduct()->increaseTotalBalance();
                $item->getQuantity() === 0 ? $cartItemRep->remove($item, true) : $cartItemRep->save($item, true);
                $productRep->save($item->getProduct(), true);
                return ResponseCreator::decreaseQuantity_ok($item->getQuantity(), $item->getProduct()->getPrice(), $item->getProduct()->getTotalBalance());
            }
        }

        return ResponseCreator::decreaseQuantity_cartItemNotFound();
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    /**
     * @throws Exception
     */
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
            if (!$item->isInOrder() && $item->getProduct()->getId() === $product->getId()) {
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
