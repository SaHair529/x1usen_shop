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
    public function __construct(private BrandRepository $brandRep, private DataMapping $dataMapping, private EntityManagerInterface $em)
    {
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
            if (!empty($lineInvalidCells)) {
                $invalidLines[$key + 1] = $lineInvalidCells;
                continue;
            }

            $newBrandEntity = new Brand($line[0], $line[1]);
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

            $newBrandEntity = new Brand($tableRowData['A'], $tableRowData['B']);
            $this->em->persist($newBrandEntity);
        }

        $this->em->flush();

        return $invalidLines;
    }

    private function validateLine(array $line)
    {
        $validationResult = [];

        if (!isset($line[0]) || empty($line[0]))
            $validationResult[] = 'Не указана модель';
        if (!isset($line[1]) || empty($line[1]))
            $validationResult[] = 'Не указан номер артикула';

        return $validationResult;
    }

    private function validateXlsTableRowData(array $tableRowData)
    {
        $validationResult = [];

        if (!isset($tableRowData['A']) || empty($tableRowData['A']))
            $validationResult[] = 'Не указана модель';

        if (!isset($tableRowData['B']) || empty($tableRowData['B']))
            $validationResult[] = 'Не указан номер артикула';

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