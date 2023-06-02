<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use JetBrains\PhpStorm\ArrayShape;
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
        $columnNums = $this->identifyColumnNumbers($fullCsv[0]);
        unset($fullCsv[0]);
        unset($fullCsv[array_key_last($fullCsv)]);
        $flush = false;
        foreach (array_filter($fullCsv) as $key => $line) {
            if ($key === array_key_last($fullCsv))
                $flush = true;
            $product = $this->prepareProductEntityByCsvRow($line, $columnNums);
            $this->productRepo->save($product, $flush);
        }
    }

    private function prepareProductEntityByCsvRow($line, $columnNums): Product
    {
        $product = new Product();
        $product->setBrand(trim($line[$columnNums['brand']]));
        $product->setName(trim($line[$columnNums['name']]));
        $product->setArticleNumber(trim($line[$columnNums['article_number']]));
        $product->setPrice(str_replace(',', '', $line[$columnNums['price']]));
        $product->setTotalBalance((float) trim($line[$columnNums['total_balance']]));
        if (isset($columnNums['measurement_unit']))
            $product->setMeasurementUnit(trim($line[$columnNums['measurement_unit']]));
        $product->setAdditionalPrice((float) trim($line[$columnNums['additional_price']]));
        $product->setImageLink(trim($line[$columnNums['image_link']]));
        if (isset($columnNums['technical_description']))
            $product->setTechnicalDescription(trim($line[$columnNums['technical_description']]));
        if (isset($columnNums['used']))
            $product->setUsed(trim($line[$columnNums['used']]) === 'новая' ? 0 : 1);

        return $product;
    }

    /**
     * Определение номеров строк для дальнейшего сопоставления с полями сущности Product
     */
    private function identifyColumnNumbers($titleCsvRow): array
    {
        $columnNums = [];
        foreach ($titleCsvRow as $num => $title) {
            $columnNums[mb_strtolower($title)] = $num;
        }

        return $columnNums;
    }
}