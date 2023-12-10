<?php

namespace App\Service;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Сервис для импорта брендов в базу данных по таблице csv/xls/xlsx
 */
class BrandsImporter
{
    private array $csvIndexes;
    private array $xlsIndexes;

    public function __construct(private DataMapping $dataMapping, private EntityManagerInterface $em, private BrandRepository $brandRep)
    {
        $this->csvIndexes = $this->dataMapping->getData('brands_csv_indexes');
        $this->xlsIndexes = $this->dataMapping->getData('brands_xls_indexes');
    }

    public function importBrandsByCsv(UploadedFile $file): array
    {
        $csvLines = explode(PHP_EOL, $file->getContent());
        $fullCsv = [];
        foreach ($csvLines as $line) {
            $fullCsv[] = str_getcsv($line);
        }

        $invalidLines = [];
        foreach (array_filter($fullCsv) as $key => $line) {
            $lineValidationResult = $this->validateLine($line);
            if (!empty($lineValidationResult)) {
                $invalidLines[$key + 1] = $lineValidationResult;
                continue;
            }

            $newBrandEntity = new Brand(trim($line[$this->csvIndexes['brand']]),
                trim($line[$this->csvIndexes['article_number']]),
                trim($line[$this->csvIndexes['model']]));

            $foundBrand = $this->brandRep->findOneBy([
                'brand' => $newBrandEntity->getBrand(),
                'model' => $newBrandEntity->getModel(),
                'article_number' => $newBrandEntity->getArticleNumber()
            ]);
            if ($foundBrand === null)
                continue;

            $this->em->persist($newBrandEntity);
        }

        $this->em->flush();

        return $invalidLines;
    }

    public function importBrandsByXls(UploadedFile $file)
    {
        $invalidLines = [];
        $spreadsheet = IOFactory::load($file);
        $brandsSheet = $spreadsheet->getActiveSheet();

        foreach ($brandsSheet->getRowIterator() as $tableRow) {
            $tableRowData = $this->prepareTableRowDataByXlsTableRow($tableRow);
            $tableRowValidationResult = $this->validateXlsTableRowData($tableRowData);
            if (!empty($tableRowValidationResult)) {
                $invalidLines[$tableRow->getRowIndex()] = $tableRowValidationResult;
                continue;
            }

            $newBrandEntity = new Brand(
                trim($tableRowData[$this->xlsIndexes['brand']]),
                trim($tableRowData[$this->xlsIndexes['article_number']]),
                trim($tableRowData[$this->xlsIndexes['model']])
            );

            $foundBrand = $this->brandRep->findOneBy([
                'brand' => $newBrandEntity->getBrand(),
                'model' => $newBrandEntity->getModel(),
                'article_number' => $newBrandEntity->getArticleNumber()
            ]);
            if ($foundBrand === null)
                continue;

            $this->em->persist($newBrandEntity);
        }

        $this->em->flush();

        return $invalidLines;
    }

    private function validateLine(array $line)
    {
        $validationResult = [];

        foreach ($this->csvIndexes as $indexName => $index) {
            if (!isset($line[$index]) || empty(trim($line[$index])))
                $validationResult[] = 'Не указан '.$indexName;
        }

        return $validationResult;
    }

    private function validateXlsTableRowData(array $tableRowData)
    {
        $validationResult = [];

        foreach ($this->xlsIndexes as $indexName => $index) {
            if (!isset($tableRowData[$index]) || empty(trim($tableRowData[$index])))
                $validationResult[] = 'Не указан '.$indexName;
        }

        return $validationResult;
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
}