<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Сервис для импорта товаров в базу данных по таблице CSV
 */
class CsvProductImporter
{
    private ProductRepository $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    /**
     * Импорт товаров из CSV
     * @param UploadedFile $file - csv-файл, который менеджер отправляет по форме в админке
     */
    public function importProducts(UploadedFile $file)
    {
        $csvLines = explode(PHP_EOL, $file->getContent());
        $fullCsv = [];
        foreach ($csvLines as $line) {
            $fullCsv[] = str_getcsv($line);
        }
        unset($fullCsv[0]);
        unset($fullCsv[array_key_last($fullCsv)]);
        $flush = false;
        foreach (array_filter($fullCsv) as $key => $line) {
            if ($key === array_key_last($fullCsv))
                $flush = true;
            $product = new Product();
            $product->setBrand($line[0]);
            $product->setName($line[1]);
            $product->setArticleNumber($line[2]);
            $product->setPrice((float) $line[3]);
            $product->setTotalBalance((float) $line[4]);
            $product->setMeasurementUnit($line[5]);
            $product->setAdditionalPrice((float) $line[6]);
            $product->setImageLink($line[7]);
            $product->setTechnicalDescription($line[8]);
            $product->setUsed($line[9] === 'новая' ? 1 : 0);
            $this->productRepo->save($product, $flush);
        }
    }
}