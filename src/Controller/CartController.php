<?php

namespace App\Controller;

use App\CustomException\ThirdParty\Dellin\CityTerminalNotFoundException;
use App\Entity\AbcpOrderCustomFieldsEntity;
use App\Repository\AbcpOrderCustomFieldsEntityRepository;
use App\Service\DataMapping;
use App\Service\ThirdParty\Abcp\AbcpApi;
use App\ControllerHelper\CartController\ResponseCreator;
use App\Entity\Order;
use App\Entity\User;
use App\Form\CreateOrderFormType;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use App\Service\ThirdParty\Alfabank\AlfabankApi;
use App\Service\ThirdParty\Dellin\DellinApi;
use App\Service\ThirdParty\Dellin\DellinProcessor;
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
    public function index(Request $req, AbcpApi $abcpApi, UrlGeneratorInterface $urlGenerator, AlfabankApi $alfabankApi, AbcpOrderCustomFieldsEntityRepository $abcpOrderCustomFieldsEntityRep, DellinApi $dellinApi): Response
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
                $shipmentAddressId =
                    $abcpApi->basketProcessor->getShipmentAddressIdByAddressName($order->getAddress(), $user)
                    ?? $abcpApi->basketProcessor->getNewAddressId($order->getAddress(), $user);
            }

            $abcpCreateOrderResponse = $abcpApi->basketProcessor->createOrder($user, $positionIds, $shipmentAddressId);
            $basketArticles = $abcpApi->basketProcessor->getBasketArticles($user);
            $abcpOrder = current($abcpCreateOrderResponse->toArray(false)['orders']);

            /** @link DataMapping::$order_ways_to_get */
            if ($order->getWayToGet() === 3) {
                $isPositionsWithDimensions = true;
                foreach ($abcpOrder['positions'] as $i => $orderPosition) {
                    $positionDescriptionArray = explode(';', $orderPosition['description']);

                    $positionDimensions = [];
                    /** @link DataMapping::$position_description_array_indexes $descriptionArrayIndexes */
                    $descriptionArrayIndexes = (new DataMapping())->getData('position_description_array_indexes');

                    if ($positionDescriptionArray[2] > 0)
                        $positionDimensions['weight'] = $positionDescriptionArray[$descriptionArrayIndexes['weight']];
                    if ($positionDescriptionArray[3] > 0)
                        $positionDimensions['length'] = $positionDescriptionArray[$descriptionArrayIndexes['length']];
                    if ($positionDescriptionArray[4] > 0)
                        $positionDimensions['width'] = $positionDescriptionArray[$descriptionArrayIndexes['width']];
                    if ($positionDescriptionArray[5] > 0)
                        $positionDimensions['height'] = $positionDescriptionArray[$descriptionArrayIndexes['height']];

                    if (!isset($positionDimensions['length']) || !isset($positionDimensions['width']) || !isset($positionDimensions['height'])) {
                        $isPositionsWithDimensions = false;
                        break;
                    }

                    # Кастомизируем структуру массива товара из abcp (добавляем габариты, которые изначально находились в описании)
                    $abcpOrder['positions'][$i]['customFields']['dimensions'] = $positionDimensions;
                }

                # Если во всех товарах указаны корректные габариты, оформляем заказ на доставку в ТК "Деловые Линии"
                if ($isPositionsWithDimensions) {
                    try {
                        (new DellinProcessor($dellinApi))->requestTransportation($abcpOrder['positions'], $user, $order);
                    }
                    catch (CityTerminalNotFoundException $e) {
                        $this->addFlash('danger', $e->getMessage());
                        return $this->redirectToRoute('order_page', [
                            'id' => $abcpOrder['number']
                        ]);
                    }
                }
            }

            /** @link DataMapping::$order_payment_types */
            if ($order->getPaymentType() === 1) {
                $host = Request::createFromGlobals();
                $domain = $host->getScheme().'://'.$host->getHost().':'.$host->getPort();

                $costInCopecks = $abcpOrder['sum']*100;
                $successPaymentUrl = $domain.$urlGenerator->generate('order_page', ['id' => $abcpOrder['number'], 'payment_result' => 'success']);
                $failedPaymentUrl = $domain.$urlGenerator->generate('order_page', ['id' => $abcpOrder['number'], 'payment_result' => 'fail']);

                $alfabankResponse = $alfabankApi->registerOrder($costInCopecks, $successPaymentUrl, $failedPaymentUrl, $abcpOrder['number']);
                $alfabankResponseData = $alfabankResponse->toArray(false);
                if (isset($alfabankResponseData['errorMessage'])) {
                    $this->addFlash('danger', 'Оформление оплаты не удалось, обратитесь к менеджеру');
                    return $this->redirectToRoute('order_page', [
                        'id' => $abcpOrder['number']
                    ]);
                }

                $abcpOrderCustomFieldsEntity = new AbcpOrderCustomFieldsEntity();
                $abcpOrderCustomFieldsEntity->setAlfabankOrderId($alfabankResponseData['orderId']);
                $abcpOrderCustomFieldsEntity->setAlfabankPaymentUrl($alfabankResponseData['formUrl']);
                $abcpOrderCustomFieldsEntity->setAbcpOrderNumber($abcpOrder['number']);

                $abcpOrderCustomFieldsEntityRep->save($abcpOrderCustomFieldsEntity, true);

                return $this->redirect($alfabankResponseData['formUrl']);
            }

            return $this->render('order/show.html.twig', [
                'order' => $abcpOrder
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
            $cartItems[$key]['articleItem'] = $abcpApi->searchProcessor->getConcreteArticleByItemKeyAndNumber($cartItem['itemKey'], $cartItem['numberFix']);
            if ($cartItems[$key]['articleItem'] === null)
                unset($cartItems[$key]);
        }

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems,
            'order_form' => $orderForm,
            'YANDEX_GEOCODER_API_KEY' => $_ENV['YANDEX_GEOCODER_API_KEY'],
            'YANDEX_SUGGEST_API_KEY' => $_ENV['YANDEX_SUGGEST_API_KEY'],
            'descriptionArrayIndexes' => (new DataMapping())->getData('position_description_array_indexes')
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
