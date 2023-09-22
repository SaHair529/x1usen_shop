<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Сервис для импорта товаров в базу данных по таблице CSV
 */
class CsvProductImporter
{
    private ProductRepository $productRepo;
    private array $requiredColumns;

    #[Pure]
    public function __construct(ProductRepository $productRepo, DataMapping $dataMapping)
    {
        $this->productRepo = $productRepo;
        $this->requiredColumns = $dataMapping->getData('import_table_title_columns');
    }

    /**
     * Импорт товаров из CSV
     * @param UploadedFile $file - csv-файл, который менеджер отправляет по форме в админке
     * @throws Exception
     */
    public function importProducts(UploadedFile $file, BrandRepository $brandRep): array
    {
        $this->resetProductsTotalBalance();

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

        $invalidLines = [];

        $autoBrands = [];
        foreach (array_filter($fullCsv) as $key => $line) {
            $lineInvalidCells = $this->validateLine($line, $key+1, $columnNums);
            if (!empty($lineInvalidCells)) {
                $invalidLines[$key + 1] = $lineInvalidCells;
                continue;
            }

            if (isset($columnNums['auto_brand']) && !in_array($autoBrand = trim($line[$columnNums['auto_brand']]), $autoBrands) && !empty($autoBrand))
                $autoBrands[] = $autoBrand;

            $productSearchAttributes = [
                'article_number' => trim($line[$columnNums['article_number']]),
                'name' => trim($line[$columnNums['name']]),
            ];
            if (isset($columnNums['used'])) {
                if ($line[$columnNums['used']] === 'новая')
                    $productSearchAttributes['used'] = 1;
                else
                    $productSearchAttributes['used'] = 0;
            }

            $product = $this->productRepo->findOneBy($productSearchAttributes);

            $product = $this->prepareProductEntityByCsvRow($line, $columnNums, $product);
            $this->productRepo->save($product, $key === array_key_last($fullCsv));
        }

        $existingBrandNames = [];
        $existingBrands = $brandRep->findBy(['brand' => $autoBrands]);
        foreach ($existingBrands as $autoBrand) {
            $existingBrandNames[] = $autoBrand->getBrand();
        }

        $brandsToAdd = array_diff($autoBrands, $existingBrandNames);
        foreach ($brandsToAdd as $key => $brandName) {
            $newBrand = new Brand();
            $newBrand->setBrand($brandName);
            $brandRep->save($newBrand, $key === array_key_last($brandsToAdd));
        }

        return $invalidLines;
    }

    #[Pure]
    private function validateTitleColumns($columns): string
    {
        $missingColumns = '';

        foreach ($this->requiredColumns as $requiredCol) {
            if (!isset($columns[$requiredCol])) {
                if (strlen($missingColumns) === 0)
                    $missingColumns .= $requiredCol;
                else
                    $missingColumns .= ", $requiredCol";
            }
        }

        return $missingColumns;
    }

    /**
     * @throws Exception
     */
    private function validateLine(array $line, int $lineNum, array $columnNums): array
    {
        $validationData = [];

        if (count($line) !== count($columnNums))
            return ['The line length does`nt match with header length'];

        foreach ($this->requiredColumns as $requiredCol) {
            if (!isset($columnNums[$requiredCol]) || empty($line[$columnNums[$requiredCol]])) {
                $validationData[] = $requiredCol;
                continue;
            }
            if (!isset($line[$columnNums[$requiredCol]]))
                $validationData[] = $requiredCol;
        }

        return $validationData;
    }

    private function prepareProductEntityByCsvRow($line, $columnNums, ?Product $product): Product
    {
        if ($product === null)
            $product = new Product();

        $product->setBrand(trim($line[$columnNums['brand']]));
        $product->setName(trim($line[$columnNums['name']]));
        $product->setArticleNumber(trim($line[$columnNums['article_number']]));
        $product->setPrice((float) str_replace(',', '', $line[$columnNums['price']]));
        $product->setTotalBalance( (float) trim($line[$columnNums['total_balance']]) );
        $product->setImageLink(trim($line[$columnNums['image_link']]));
        if (isset($columnNums['auto_brand']))
            $product->setAutoBrand(trim($line[$columnNums['auto_brand']]));
        if (isset($columnNums['auto_model']))
            $product->setAutoModel(trim($line[$columnNums['auto_model']]));
        if (isset($columnNums['category']))
            $product->setCategory(trim($line[$columnNums['category']]));
        if (isset($columnNums['measurement_unit']))
            $product->setMeasurementUnit(trim($line[$columnNums['measurement_unit']]));
        if (isset($columnNums['additional_price']))
            $product->setAdditionalPrice((float) trim($line[$columnNums['additional_price']]));
        if (isset($columnNums['technical_description']))
            $product->setTechnicalDescription(trim($line[$columnNums['technical_description']]));
        if (isset($columnNums['used']))
            $product->setUsed(trim($line[$columnNums['used']]) === 'новая' ? 0 : 1);
        if (isset($columnNums['additional_images_links']))
            $product->setAdditionalImagesLinks(trim($line[$columnNums['additional_images_links']]));
        if (isset($columnNums['length']))
            $product->setLength($line[$columnNums['length']]);
        if (isset($columnNums['width']))
            $product->setWidth($line[$columnNums['width']]);
        if (isset($columnNums['height']))
            $product->setHeight($line[$columnNums['height']]);

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

    private function resetProductsTotalBalance()
    {
        $allProducts = $this->productRepo->findAll();

        foreach ($allProducts as $index => $product) {
            $product->setTotalBalance(0);
            $this->productRepo->save($product, $index === count($allProducts) - 1);
        }
    }
}