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

    public function getDataValue($dataContainer, $dataKey)
    {
        return $this->$dataContainer[$dataKey];
    }
}