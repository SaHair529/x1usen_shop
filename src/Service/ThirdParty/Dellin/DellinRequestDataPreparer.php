<?php

namespace App\Service\ThirdParty\Dellin;

class DellinRequestDataPreparer
{
    public function prepareConsolidatedCargoTransportationData(
        string $appKey,
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
        string $receiverName
    ): array
    {
        return [
            'appkey' => $appKey,
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
                    'time' => [ # todo Заменить тестовое время на реальное
                        'worktimeStart' => '12:00',
                        'worktimeEnd' => '21:00'
                    ]
                ],
                'arrival' => [
                    'variant' => 'address',
                    'address' => [
                        'search' => $arrivalAddress
                    ],
                    'time' => [ # todo Заменить тестовое время на реальное
                        'worktimeStart' => '16:00',
                        'worktimeEnd' => '16:30'
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
}