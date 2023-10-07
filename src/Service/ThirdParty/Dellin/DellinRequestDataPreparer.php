<?php

namespace App\Service\ThirdParty\Dellin;

use App\Entity\CartItem;
use App\Repository\DellinTerminalRepository;
use DateInterval;
use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpKernel\KernelInterface;

class DellinRequestDataPreparer
{
    public function __construct(private KernelInterface $kernel, private DellinTerminalRepository $terminalRep)
    {
    }

    /**
     * @param string $sessionId
     * @param string $derivalAddress
     * @param string $city
     * @param string $arrivalAddress
     * @param string $companyOwnerFullname
     * @param string $companyINN
     * @param string $companyContactPhone
     * @param string $receiverPhone
     * @param string $receiverName
     * @param CartItem[] $cartItems
     * @param string $arrivalAddressCoords
     * @param int $deliveryType (По умолчанию - До терминала)
     * @return array
     * @throws Exception
     */
    #[ArrayShape(['appkey' => "mixed", 'sessionID' => "string", 'inOrder' => "false", 'delivery' => "array", 'cargo' => "array", 'members' => "array", 'payment' => "string[]"])]
    public function prepareConsolidatedCargoTransportationRequestData(
        string $sessionId,
        string $derivalAddress, string $city, string $arrivalAddress,
        string $companyOwnerFullname, string $companyINN, string $companyContactPhone,
        string $receiverPhone, string $receiverName,
        array  $cartItems, string $arrivalAddressCoords, int $deliveryType = 2
    ): array
    {
        $tomorrowDate = (new DateTime())->add(new DateInterval('P1D'));

        $deliveryProduceDate = $tomorrowDate->format('Y-m-d');
        $cargoMaxLength = 0;
        $cargoMaxWidth = 0;
        $cargoMaxHeight = 0;
        $cargoWeight = 0;
        $cargoTotalWeight = 0;
        $cargoTotalVolume = 0;
        $derivalWorktimeStart = '10:00';
        $derivalWorktimeEnd = '21:00';
        $arrivalWorktimeStart = '10:00'; # todo
        $arrivalWorktimeEnd = '21:00'; # todo

        foreach ($cartItems as $cartItem) {
            if ($cargoMaxLength < $productLength = $cartItem->getProduct()->getLength())
                $cargoMaxLength = $productLength;
            if ($cargoMaxWidth < $productWidth = $cartItem->getProduct()->getWidth())
                $cargoMaxWidth = $productWidth;
            if ($cargoMaxHeight < $productHeight = $cartItem->getProduct()->getHeight())
                $cargoMaxHeight = $productHeight;
            if ($cargoWeight < $productWeight = $cartItem->getProduct()->getWeight())
                $cargoWeight = $productWeight;
            $cargoTotalWeight += $cartItem->getProduct()->getWeight();
            $cargoTotalVolume +=
                $cartItem->getProduct()->getLength() *
                $cartItem->getProduct()->getWidth() *
                $cartItem->getProduct()->getHeight();
        }

        $result = [
            'appkey' => $_ENV['DELLIN_APP_KEY'],
            'sessionID' => $sessionId,
            'inOrder' => false, # todo поменять на true после тестов
            'delivery' => [
                'deliveryType' => [
                    'type' => 'auto'
                ],
                'derival' => [
                    'produceDate' => $deliveryProduceDate,
                    'variant' => 'address',
                    'address' => [
                        'search' => $derivalAddress
                    ],
                    'time' => [
                        'worktimeStart' => $derivalWorktimeStart,
                        'worktimeEnd' => $derivalWorktimeEnd
                    ]
                ],
                'arrival' => [
                    'time' => [
                        'worktimeStart' => $arrivalWorktimeStart,
                        'worktimeEnd' => $arrivalWorktimeEnd
                    ]
                ]
            ],
            'cargo' => [
                'quantity' => '1',
                'length' => $cargoMaxLength,
                'width' => $cargoMaxWidth,
                'height' => $cargoMaxHeight,
                'weight' => $cargoWeight,
                'totalVolume' => $cargoTotalVolume,
                'totalWeight' => $cargoTotalWeight,
                'freightUID' => '0x982400215e7024d411e1e844ef594aad' # todo ?
            ],
            'members' => [
                'requester' => [
                    'role' => 'sender',
                    'uid' => '00000000-0000-0000-0000-000000000000' # todo ?
                ],
                'sender' => [
                    'counteragent' => [
                        'form' => '0xaa9042fea4fa169d4d021c6941f2090f', # todo ?
                        'name' => $companyOwnerFullname,
                        'inn' => $companyINN
                    ],
                    'contactPersons' => [
                        [
                            'name' => $companyOwnerFullname
                        ]
                    ],
                    'phoneNumbers' => [
                        [
                            'number' => $companyContactPhone
                        ]
                    ]
                ],
                'receiver' => [
                    'counteragent' => [
                        'form' => '0xab91feea04f6d4ad48df42161b6c2e7a',
                        'isAnonym' => true,
                        'phone' => $receiverPhone,
                        'name' => $receiverName
                    ]
                ]
            ],
            'payment' => [
                'type' => 'cash',
                'primaryPayer' => 'receiver'
            ]
        ];

        if ($deliveryType === 1) { # Если способ доставки "По адресу"
            $result['delivery']['arrival']['variant'] = 'address';
            $result['delivery']['arrival']['address']['search'] = $arrivalAddress;
        }
        else { # Если способ доставки "До терминала"
            $result['delivery']['arrival']['variant'] = 'terminal';
            $result['delivery']['arrival']['terminalID'] = $this->detectClosestTerminalByAddressCoords($arrivalAddressCoords, $city);
        }

        return $result;
    }

    /**
     * @param string $sessionId
     * @param $cartItems
     * @param string $derivalAddress
     * @param array $requestData
     * @return array
     */
    #[ArrayShape(['appkey' => "mixed", 'sessionID' => "string", 'delivery' => "array", 'cargo' => "array"])]
    public function prepareCostAndDeliveryTimeCalculatorData(string $sessionId, $cartItems, string $derivalAddress, array $requestData): array
    {
        $tomorrowDate = (new DateTime())->add(new DateInterval('P1D'));

        $produceDate = $tomorrowDate->format('Y-m-d');
        $cargoMaxLength = 0;
        $cargoMaxWidth = 0;
        $cargoMaxHeight = 0;
        $cargoWeight = 0;
        $cargoTotalWeight = 0;
        $cargoTotalVolume = 0;
        $derivalWorktimeStart = '10:00';
        $derivalWorktimeEnd = '21:00';
        $arrivalWorktimeStart = '10:00'; # todo
        $arrivalWorktimeEnd = '21:00'; # todo

        foreach ($cartItems as $cartItem) {
            if (str_contains($requestData['checkedCartItemsIds'], $cartItem->getId())) {
                if ($cargoMaxLength < $productLength = $cartItem->getProduct()->getLength())
                    $cargoMaxLength = $productLength;
                if ($cargoMaxWidth < $productWidth = $cartItem->getProduct()->getWidth())
                    $cargoMaxWidth = $productWidth;
                if ($cargoMaxHeight < $productHeight = $cartItem->getProduct()->getHeight())
                    $cargoMaxHeight = $productHeight;
                if ($cargoWeight < $productWeight = $cartItem->getProduct()->getWeight())
                    $cargoWeight = $productWeight;
                $cargoTotalWeight += $cartItem->getProduct()->getWeight();
                $cargoTotalVolume +=
                    $cartItem->getProduct()->getLength() *
                    $cartItem->getProduct()->getWidth() *
                    $cartItem->getProduct()->getHeight();
            }
        }

        return [
            'appkey' => $_ENV['DELLIN_APP_KEY'],
            'sessionID' => $sessionId,
            'delivery' => [
                'deliveryType' => [
                    'type' => 'auto'
                ],
                'derival' => [
                    'produceDate' => $produceDate,
                    'variant' => 'address',
                    'address' => [
                        'search' => $derivalAddress
                    ],
                    'time' => [
                        'worktimeStart' => $derivalWorktimeStart,
                        'worktimeEnd' => $derivalWorktimeEnd
                    ]
                ],
                'arrival' => [
                    'variant' => 'address',
                    'address' => [
                        'search' => "{$requestData['city']}, {$requestData['address']}"
                    ],
                    'time' => [
                        'worktimeStart' => $arrivalWorktimeStart,
                        'worktimeEnd' => $arrivalWorktimeEnd
                    ]
                ]
            ],
            'cargo' => [
                'quantity' => '1',
                'length' => $cargoMaxLength,
                'width' => $cargoMaxWidth,
                'height' => $cargoMaxHeight,
                'weight' => $cargoWeight,
                'totalVolume' => $cargoTotalVolume,
                'totalWeight' => $cargoTotalWeight,
                'freightUID' => '0x982400215e7024d411e1e844ef594aad'
            ]
        ];
    }

    /**
     * Определение близжайшего терминала к указанным в $coords широте и долготе
     * @param string $coords 'широта:долгота'
     * @throws Exception
     */
    public function detectClosestTerminalByAddressCoords(string $coords, string $city): int
    {
        $cityTerminals = $this->terminalRep->findBy(['city' => $city]);
        $minDistance = PHP_FLOAT_MAX;
        $closestTerminalId = $cityTerminals[0]->getTerminalId();
        [$targetLatitude, $targetLongitude] = explode(':', $coords);

        foreach ($cityTerminals as $terminal) {
            $distance = $this->haversine($targetLatitude, $targetLongitude, $terminal->getLatitude(), $terminal->getLongitude());
            if ($minDistance > $distance) {
                $minDistance = $distance;
                $closestTerminalId = $terminal->getTerminalId();
            }
        }

        return $closestTerminalId;
    }

    /**
     * Определение расстояния между двумя координатами по формуле Гаверсинуса
     * {@see https://en.wikipedia.org/wiki/Haversine_formula}
     * @param float|int $lat1
     * @param float|int $lon1
     * @param float|int $lat2
     * @param float|int $lon2
     * @return float|int
     */
    function haversine(float|int $lat1, float|int $lon1, float|int $lat2, float|int $lon2): float|int
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1) * cos($lat2) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $radius = 6371; // Средний радиус Земли в километрах
        return $radius * $c;
    }
}