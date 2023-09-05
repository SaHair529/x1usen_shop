<?php

namespace App\Service\ThirdParty\Dellin;

use App\Entity\CartItem;
use DateInterval;
use DateTime;
use JetBrains\PhpStorm\ArrayShape;

class DellinRequestDataPreparer
{
    public function prepareConsolidatedCargoTransportationData(
        string $sessionId,
        string $deliveryProduceDate,
        string $derivalAddress,
        string $arrivalAddress,
        string $cargoMaxLength,
        string $cargoMaxWidth,
        string $cargoMaxHeight,
        string $cargoWeight,
        string $cargoTotalWeight,
        string $cargoTotalVolume,
        string $requesterUID,
        string $senderFullname,
        string $senderINN,
        string $senderContactPersonName,
        string $senderContactPersonPhone,
        string $receiverPhone,
        string $receiverName,
        string $derivalWorktimeStart,
        string $derivalWorktimeEnd,
        string $arrivalWorktimeStart,
        string $arrivalWorktimeEnd
    ): array
    {
        return [
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
                    'variant' => 'address',
                    'address' => [
                        'search' => $arrivalAddress
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
                'freightUID' => '0x982400215e7024d411e1e844ef594aad' # todo ?
            ],
            'members' => [
                'requester' => [
                    'role' => 'sender',
                    'uid' => $requesterUID # todo ?
                ],
                'sender' => [
                    'counteragent' => [
                        'form' => '0xaa9042fea4fa169d4d021c6941f2090f', # todo ?
                        'name' => $senderFullname,
                        'inn' => $senderINN
                    ],
                    'contactPersons' => [
                        [
                            'name' => $senderContactPersonName
                        ]
                    ],
                    'phoneNumbers' => [
                        [
                            'number' => $senderContactPersonPhone
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
                'type' => 'noncash',
                'primaryPayer' => 'receiver'
            ]
        ];
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
}