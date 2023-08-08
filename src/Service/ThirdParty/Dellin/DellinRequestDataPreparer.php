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
        string $receiverName,
        string $derivalWorktimeStart,
        string $derivalWorktimeEnd,
        string $arrivalWorktimeStart,
        string $arrivalWorktimeEnd
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
}