<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use Exception;
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
     * @throws Exception
     */
    public function importProducts(UploadedFile $file, BrandRepository $brandRep)
    {
        $csvLines = explode(PHP_EOL, $file->getContent());
        $fullCsv = [];
        foreach ($csvLines as $line) {
            $fullCsv[] = str_getcsv($line);
        }
        $columnNums = $this->identifyColumnNumbers($fullCsv[0]);

        $missingColumns = $this->validateTitleColumns($columnNums);
        if (!empty($missingColumns))
            throw new Exception('Невалидные заголовки таблицы. Проверьте на наличие следующих заголовков: '.$missingColumns);

        unset($fullCsv[0]);
        unset($fullCsv[array_key_last($fullCsv)]);

        $brands = [];
        foreach (array_filter($fullCsv) as $key => $line) {
            if (!in_array($brand = trim($line[$columnNums['brand']]), $brands))
                $brands[] = $brand;

            $product = $this->prepareProductEntityByCsvRow($line, $columnNums);
            $this->productRepo->save($product, $key === array_key_last($fullCsv));
        }

        $existingBrandNames = [];
        $existingBrands = $brandRep->findBy(['brand' => $brands]);
        foreach ($existingBrands as $brand) {
            $existingBrandNames[] = $brand->getBrand();
        }

        $brandsToAdd = array_diff($brands, $existingBrandNames);
        foreach ($brandsToAdd as $key => $brandName) {
            $newBrand = new Brand();
            $newBrand->setBrand($brandName);
            $brandRep->save($newBrand, $key === array_key_last($brandsToAdd));
        }
    }

    private function validateTitleColumns($columns): string
    {
        $missingColumns = '';
        if (!isset($columns['brand']))
            $missingColumns .= 'brand';
        if (!isset($columns['image_link']))
            $missingColumns .= 'image_link';

        return $missingColumns;
    }

    private function prepareProductEntityByCsvRow($line, $columnNums): Product
    {
        $product = new Product();
        $product->setBrand(trim($line[$columnNums['brand']]));
        $product->setName(trim($line[$columnNums['name']]));
        $product->setArticleNumber(trim($line[$columnNums['article_number']]));
        $product->setPrice((float) str_replace(',', '', $line[$columnNums['price']]));
        $product->setTotalBalance((float) trim($line[$columnNums['total_balance']]));
        if (isset($columnNums['measurement_unit']))
            $product->setMeasurementUnit(trim($line[$columnNums['measurement_unit']]));
        if (isset($columnNums['additional_price']))
            $product->setAdditionalPrice((float) trim($line[$columnNums['additional_price']]));
        $product->setImageLink(trim($line[$columnNums['image_link']]));
        if (isset($columnNums['technical_description']))
            $product->setTechnicalDescription(trim($line[$columnNums['technical_description']]));
        if (isset($columnNums['used']))
            $product->setUsed(trim($line[$columnNums['used']]) === 'новая' ? 0 : 1);
        if (isset($columnNums['additional_images_links']))
            $product->setAdditionalImagesLinks(trim($line[$columnNums['additional_images_links']]));

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