<?php

namespace App\Service;

/**
 * Словарь статичных данных. Например, статусы в БД у сущности order имеют integer тип.
 * Этот класс даёт лейбл каждому из статусов
 */
class DataMapping
{
    private array $admin_ids = [3];

    private array $order_statuses = [
        '1' => 'В обработке',
        '2' => 'Ожидание оплаты',
        '3' => 'Готово к отгрузке',
        '4' => 'Ожидание поступления',
        '5' => 'Доставлено',
        '6' => 'Нет в наличии'
    ];

    private array $order_ways_to_get = [
        '1' => 'Самовывоз',
        '2' => 'Доставка по СПБ',
        '3' => 'Доставка по РФ'
    ];

    private array $order_delivery_types = [
        '1' => 'По адресу',
        '2' => 'До терминала'
    ];

    private array $order_payment_types = [
        '1' => 'Картой через сайт',
        '2' => 'Наличными'
    ];

    private array $notification_actions = [
        '1' => 'Статус заказа изменён',
        '2' => 'Новый комментарий'
    ];

    private array $google_services_credentials_filenames = [
        'gmail' => 'gmail_credentials.json'
    ];

    private array $google_services_accesstoken_filenames = [
        'gmail' => 'gmail_accesstoken.json'
    ];

    private array $igg_thirdparty_data = [
        'email' => 'sa.hairulaev@gmail.com'
    ];

    private array $import_table_title_columns = [
        'brand', 'name', 'article_number', 'price', 'total_balance',
        'image_link', 'width', 'length', 'height'
    ];

    private string $companyStockAddress = 'Санкт-Петербург, ул Уральская, д 4';

    public function getData($dataContainer): array|string
    {
        return $this->$dataContainer;
    }

    public function getValueByKey($dataContainer, $dataKey)
    {
        return $this->$dataContainer[$dataKey];
    }

    public function getKeyByValue($dataContainer, $value): bool|int|string
    {
        return array_search($value, $this->$dataContainer);
    }
}