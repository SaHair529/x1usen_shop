<?php

namespace App\Service;

use GuayaquilLib\ServiceAm;

class LaximoAPIWrapper
{
    private ServiceAm $am;

    public function __construct()
    {
        $this->am = new ServiceAm(getenv('AM_LOGIN'), getenv('AM_PASSWORD'));
    }

    /**
     * @return string[]
     */
    public function getReplacements(string $oem): array
    {
        $result = [];
        //            $details = $amService->findOem($queryStr, '', # todo uncomment
//                ['crosses', 'weights', 'names', 'properties', 'images'],
//                ['synonym', 'PartOfTheWhole', 'Replacement', 'Duplicate', 'Tuning', 'Bidirectional'],
//            );

//            file_put_contents(__DIR__.'/serialized_details.txt', serialize($details)); # todo remove
        $details = unserialize(file_get_contents(__DIR__.'/../../serialized_data/serialized_details.txt')); # todo remove
        foreach ($details->getOems() as $detail) {
            $replacements = $detail->getReplacements();
            foreach($replacements as $replacement) {
                $replacementOem = $replacement->getPart()->getOem();
                if (!in_array($replacementOem, $result))
                    $result[] = $replacementOem;
            }
        }
        if (array_search($oem, $result))
            unset($result[array_search($oem, $result)]);

        return array_values($result);
    }
}