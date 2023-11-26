<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\BasketActions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Обертка для класса @link BasketActions
 */
class BasketProcessor
{
    public function __construct(private BasketActions $basketActions){}

    /**
     * Добавление товара в корзину ABCP
     * @param User $user
     * @param array $article
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function addArticleToBasket(User $user, array $article): ResponseInterface
    {
        return $this->basketActions->add([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'positions' => [
                [
                    'brand' => $article['brand'],
                    'number' => $article['number'],
                    'itemKey' => $article['itemKey'],
                    'supplierCode' => $article['supplierCode'],
                    'quantity' => 1
                ]
            ]
        ]);
    }

    /**
     * Указание количества товаров в корзине (Обычно используется для уменьшения количества на 1)
     * @param int $targetQuantity
     * @param array $article
     * @param User $user
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function setArticleQuantity(int $targetQuantity, array $article, User $user): ResponseInterface
    {
        $requestData = [
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'positions' => [
                [
                    'brand' => $article['brand'],
                    'number' => $article['number'],
                    'itemKey' => $article['itemKey'],
                    'supplierCode' => $article['supplierCode'],
                    'quantity' => 0
                ]
            ]
        ];

        $abcpResponse = $this->basketActions->add($requestData);

        if ($targetQuantity === 0)
            return $abcpResponse;

        $requestData['positions'][0]['quantity'] = $targetQuantity;

        return $this->basketActions->add($requestData);
    }

    /**
     * @param User $user
     * @param string $itemKey
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getArticleFromBasket(User $user, string $itemKey): array
    {
        $userBasketArticles = $this->basketActions->content([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5()
        ])->toArray();

        foreach ($userBasketArticles as $basketArticle) {
            if ($basketArticle['itemKey'] === $itemKey)
                return $basketArticle;
        }

        return [
            'quantity' => 0
        ];
    }

    public function getBasketArticles(User $user): array
    {
        return $this->basketActions->content([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5()
        ])->toArray(false);
    }

    /**
     * Оформление заказа
     */
    public function createOrder(User $user, array $positionIds, int $shipmentAddress)
    {
        return $this->basketActions->order([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'shipmentAddress' => $shipmentAddress,
            'positionIds' => $positionIds
        ]);
    }

    /**
     * Получение адресов корзины
     * @param User $user
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getShipmentAddresses(User $user): array
    {
        return $this->basketActions->shipmentAddresses([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5()
        ])->toArray(false);
    }

    /**
     * Получение id адреса по его названию
     * @param string $targetAddress - Название адреса
     * @param User $user - Пользователь, в чьей корзине будет вестись поиск искомого адреса
     * @return int|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getShipmentAddressIdByAddressName(string $targetAddress, User $user): ?int
    {
        $shipmentAddresses = $this->getShipmentAddresses($user);

        foreach ($shipmentAddresses as $address) {
            if ($targetAddress === $address['name'])
                return $address['id'];
        }

        return null;
    }

    /**
     * @param string $address
     * @param User $user
     * @return int
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getNewAddressId(string $address, User $user): int
    {
        return $this->basketActions->shipmentAddress([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'address' => $address
        ])->toArray(false)['shipmentAddressId'];
    }

    /**
     * Получение заказа по number
     * @param User $user
     * @param int $id
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getOrderByNumber(User $user, int $id): array
    {
        $orders = $this->basketActions->ordersList([
            'userlogin' => $user->getAbcpUserCode(),
            'userpsw' => $user->getPasswordMd5(),
            'orders' => [$id]
        ])->toArray(false);

        return current($orders);
    }
}