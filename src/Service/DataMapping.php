<?php

namespace App\Service;

/**
 * Словарь статичных данных. Например, статусы в БД у сущности order имеют integer тип.
 * Этот класс даёт лейбл каждому из статусов
 */
class DataMapping
{
    private array $admin_ids = [3];

    private array $user_client_types = [
        '1' => 'Физ. лицо',
    ];

    private array $order_statuses = [
        '1' => 'В обработке',
        '3' => 'Готово к отгрузке',
        '4' => 'Ожидание поступления',
        '5' => 'Доставлено',
        '6' => 'Нет в наличии',
        '7' => 'Требуется заказ вручную',
    ];

    private array $order_payment_statuses = [
        '-1'    => 'Платеж не прошел',
        '0'     => 'Ожидание оплаты',
        '1'     => 'Оплата прошла успешно'
    ];

    private array $order_ways_to_get = [
        '1' => 'Самовывоз',
        '2' => 'Доставка по СПБ',
        '3' => 'Доставка по РФ (ТК "Деловые линии")'
    ];

    private array $order_delivery_types = [
        '1' => 'По адресу',
        '2' => 'До терминала'
    ];

    private array $order_payment_types = [
        '1' => 'Картой через сайт',
        '2' => 'Наличными (при получении)'
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
        'name', 'article_number', 'price', 'total_balance',
        'image_link' /*'width', 'length', 'height', 'brand'*/
    ];

    private array $abcp_organisation_types = [
        '1' => 'Автосервис',
        '2' => 'Автомагазин',
        '3' => 'Собственный автопарк'
    ];

    private array $abcp_juridical_entity_types = [
        'ООО' => 'ООО',
        'ОАО' => 'ОАО',
        'ЗАО' => 'ЗАО',
        'ТОО' => 'ТОО',
        'АО' => 'АО',
        'ЧП' => 'ЧП',
        'ПБОЮЛ' => 'ПБОЮЛ'
    ];

    private string $companyINN = '7810902553';
    private string $companyKPP = '781001001';
    private string $companyOGRN = '1207800112519';

    private string $companyCheckingAccount = '40702810932400002782';
    private string $companyBank = 'ФИЛИАЛ "САНКТ-ПЕТЕРБУРГСКИЙ" АО "АЛЬФА-БАНК"';
    private string $companyBIK = '044030786';
    private string $companyCorporationAccount = '30101810600000000786';

    private string $companyJuridicalAddress = 'улица Благодатная, д. Д. 65, корп./ст. ЛИТЕР А, кв./оф.';
    private string $companyContactPhone = '+79958907742';
    private string $companyOwnerFullname = 'Ибрагимов Ибрагим Нурмагомедович';

    private string $companyManagerContactPhone = '+7 (995) 890-77-42';
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