<?php

namespace App\Service;

use App\Entity\Brand;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JetBrains\PhpStorm\Pure;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Сервис для импорта товаров в базу данных по таблице CSV
 */
class ProductImporter
{
    private ProductRepository $productRepo;
    private array $requiredColumns;

    #[Pure]
    public function __construct(ProductRepository $productRepo, DataMapping $dataMapping, private EntityManagerInterface $entityManager)
    {
        $this->productRepo = $productRepo;
        $this->requiredColumns = $dataMapping->getData('import_table_title_columns');
    }

    /**
     * Импорт товаров из CSV
     * @param UploadedFile $file - csv-файл, который менеджер отправляет по форме в админке
     * @throws Exception
     */
    public function importProductsByCsv(UploadedFile $file, BrandRepository $brandRep): array
    {
        $this->resetProductsTotalBalance();

        $csvLines = explode(PHP_EOL, $file->getContent());
        $fullCsv = [];
        foreach ($csvLines as $line) {
            $fullCsv[] = str_getcsv($line);
        }
        $columnNums = $this->identifyTableRowMapByTitleTableRow($fullCsv[0]);

        $missingColumns = $this->validateTitleColumns($columnNums);
        if (!empty($missingColumns))
            throw new Exception('Невалидные заголовки таблицы. Проверьте на наличие следующих заголовков: '.$missingColumns);

        unset($fullCsv[0]);
        unset($fullCsv[array_key_last($fullCsv)]);

        $invalidLines = [];

        $autoBrands = [];
        foreach (array_filter($fullCsv) as $key => $line) {
            $lineInvalidCells = $this->validateLine($line, $columnNums);
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

            $product = $this->prepareProductEntityByTableRow($line, $columnNums, $product);
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

    /**
     * Импорт товаров из XLS или XLSX
     * @param UploadedFile $file
     * @param BrandRepository $brandRep
     * @return array
     * @throws Exception
     */
    public function importProductsByXls(UploadedFile $file, BrandRepository $brandRep): array
    {
        $invalidLines = [];
        $spreadsheet = IOFactory::load($file);
        $productsSheet = $spreadsheet->getActiveSheet();

        $titleTableRow = $productsSheet->getRowIterator(1, 1)->current();
        $rowMap = $this->identifyTableRowMapByTitleTableRow($titleTableRow);
        $highestRowIndex = $productsSheet->getHighestRow();
        $autoBrandNames = [];

        foreach ($productsSheet->getRowIterator(2) as $tableRow) {
            $missingTitleColumns = $this->validateTitleColumns($rowMap);
            if (!empty($missingTitleColumns))
                throw new Exception('Невалидные заголовки таблицы. Проверьте на наличие следующих заголовков: '.$missingTitleColumns);

            $tableRowData = $this->prepareTableRowDataByXlsTableRow($tableRow);
            $lineInvalidCells = $this->validateLine($tableRowData, $rowMap);
            if (!empty($lineInvalidCells)) {
                $invalidLines[$tableRow->getRowIndex()] = $lineInvalidCells;
                continue;
            }

            if (isset($rowMap['auto_brand'])) {
                $autoBrandName = trim($tableRowData[$rowMap['auto_brand']]);
                if (!empty($autoBrandName) && !in_array($autoBrandName, $autoBrandNames))
                    $autoBrandNames[] = $autoBrandName;
            }

            $product = $this->prepareProductEntityByTableRow($tableRowData, $rowMap);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();

        $existingAutoBrandNames = [];
        $existingAutoBrands = $brandRep->findBy(['brand' => $autoBrandNames]);
        foreach ($existingAutoBrands as $autoBrandName)
            $existingAutoBrandNames[] = $autoBrandName->getBrand();
        $brandNamesToAdd = array_diff($autoBrandNames, $existingAutoBrandNames);
        foreach ($brandNamesToAdd as $key => $brandName) {
            $newBrand = new Brand();
            $newBrand->setBrand($brandName);
            $brandRep->save($newBrand, $key === array_key_last($brandNamesToAdd));
        }

        return $invalidLines;
    }

    private function prepareTableRowDataByXlsTableRow(Row $xlsTableRow): array
    {
        $result = [];
        $tableCellIterator = $xlsTableRow->getCellIterator();
        $tableCellIterator->setIterateOnlyExistingCells(true);

        foreach ($tableCellIterator as $cellLetter => $tableCell)
            $result[$cellLetter] = $tableCell->getValue();

        return $result;
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
    private function validateLine(array $line, array $rowMap): array
    {
        $validationData = [];

        if (count($line) !== count($rowMap))
            return ['The line length does`nt match with header length'];

        foreach ($rowMap as $requiredCol => $requiredTableCellLetter) {
            if (!isset($line[$requiredTableCellLetter]))
                $validationData[] = $requiredCol;
        }

        foreach ($this->requiredColumns as $requiredCol) {
            if (!isset($line[$rowMap[$requiredCol]]) || empty($line[$rowMap[$requiredCol]]))
                $validationData[] = $requiredCol;
        }

        return $validationData;
    }

    private function prepareProductEntityByTableRow($tableRowData, $rowMap): Product
    {
        $productSearchAttributes = [
            'article_number' => trim($tableRowData[$rowMap['article_number']]),
            'name' => trim($tableRowData[$rowMap['name']]),
        ];

        if (isset($rowMap['used'])) {
            if ($tableRowData[$rowMap['used']] === 'новая')
                $productSearchAttributes['used'] = 1;
            else
                $productSearchAttributes['used'] = 0;
        }

        $product = $this->productRepo->findOneBy($productSearchAttributes)
            ?? new Product();

        $product->setBrand(trim($tableRowData[$rowMap['brand']]));
        $product->setName(trim($tableRowData[$rowMap['name']]));
        $product->setArticleNumber(trim($tableRowData[$rowMap['article_number']]));
        $product->setPrice((float) str_replace(',', '', $tableRowData[$rowMap['price']]));
        $product->setTotalBalance( (float) trim($tableRowData[$rowMap['total_balance']]) );
        $product->setImageLink(trim($tableRowData[$rowMap['image_link']]));
        if (isset($rowMap['auto_brand']))
            $product->setAutoBrand(trim($tableRowData[$rowMap['auto_brand']]));
        if (isset($rowMap['auto_model']))
            $product->setAutoModel(trim($tableRowData[$rowMap['auto_model']]));
        if (isset($rowMap['category']))
            $product->setCategory(trim($tableRowData[$rowMap['category']]));
        if (isset($rowMap['measurement_unit']))
            $product->setMeasurementUnit(trim($tableRowData[$rowMap['measurement_unit']]));
        if (isset($rowMap['additional_price']))
            $product->setAdditionalPrice((float) trim($tableRowData[$rowMap['additional_price']]));
        if (isset($rowMap['technical_description']))
            $product->setTechnicalDescription(trim($tableRowData[$rowMap['technical_description']]));
        if (isset($rowMap['used']))
            $product->setUsed(trim($tableRowData[$rowMap['used']]) === 'новая' ? 0 : 1);
        if (isset($rowMap['additional_images_links']))
            $product->setAdditionalImagesLinks(trim($tableRowData[$rowMap['additional_images_links']]));
        if (isset($rowMap['length']))
            $product->setLength($tableRowData[$rowMap['length']]);
        if (isset($rowMap['width']))
            $product->setWidth($tableRowData[$rowMap['width']]);
        if (isset($rowMap['height']))
            $product->setHeight($tableRowData[$rowMap['height']]);

        return $product;
    }

    /**
     * Подготовка словаря с названием поля Product в качестве ключа и буквой в таблице в качестве значения
     */
    private function identifyTableRowMapByTitleTableRow(Row $titleRow): array
    {
        $columnNums = [];

        foreach ($titleRow->getCellIterator() as $cellLetter => $title) {
            $columnNums[$cellLetter] = $title->getValue();
        }

        return array_flip(array_filter($columnNums));
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