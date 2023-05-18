<?php

namespace App\Service;

class DataMapping
{
    private array $order_statuses = [
        '1' => 'Ожидание оплаты'
    ];

    public function getData($dataContainer): array
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