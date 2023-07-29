<?php

namespace App\Service;

use Exception;
use GuayaquilLib\ServiceAm;

class LaximoAPIWrapper
{
    private ServiceAm $am;

    public function __construct()
    {
        $this->am = new ServiceAm($_ENV['AM_LOGIN'], $_ENV['AM_PASSWORD']);
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function getReplacements(string $oem): array
    {
        $result = [];
        $details = $this->am->findOem($oem, '',
            ['crosses', 'weights', 'names', 'properties', 'images'],
            ['synonym', 'PartOfTheWhole', 'Replacement', 'Duplicate', 'Tuning', 'Bidirectional'],
        );

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