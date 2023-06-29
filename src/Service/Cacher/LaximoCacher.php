<?php

namespace App\Service\Cacher;

use DateInterval;
use GuayaquilLib\objects\oem\VehicleObject;

class LaximoCacher extends BaseCacher
{
    public function getVehicleObjectByVin(string $vin): ?VehicleObject
    {
        return $this->getItem($vin);
    }

    public function setVehicleData(VehicleObject $vehicle, string $vin)
    {
        $item = $this->memcached->getItem($vin);
        $item->set($vehicle);
        $item->expiresAfter(new DateInterval('PT23H50M'));
        $this->memcached->save($item);
    }
}