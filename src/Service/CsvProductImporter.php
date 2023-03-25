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

    /**
     * Словарь, с помощью которого можно определять
     * к какому полю сущности Product относится та или иная колонка таблицы по заголовку колонки (первой строчке)
     */
    private const TABLE_TITLE_TO_PRODUCT_FIELD = [
        'производитель' => 'brand',
        'наименование' => 'name',
        'артикул/oem' => 'article_number',
        'цена' => 'price',
        'общий остаток' => 'total_balance',
        'ед измерения' => 'measurement_unit',
        'цена по доп.прайс-листу' => 'additional_price',
        'ссылка на изображение' => 'image_link',
        'тех.описание' => 'technical_description',
        'бу или новые' => 'used'
    ];

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
        $product->setBrand($line[$columnNums['brand']]);
        $product->setName($line[$columnNums['name']]);
        $product->setArticleNumber($line[$columnNums['article_number']]);
        $product->setPrice((float) $line[$columnNums['price']]);
        $product->setTotalBalance((float) $line[$columnNums['total_balance']]);
        $product->setMeasurementUnit($line[$columnNums['measurement_unit']]);
        $product->setAdditionalPrice((float) $line[$columnNums['additional_price']]);
        $product->setImageLink($line[$columnNums['image_link']]);
        $product->setTechnicalDescription($line[$columnNums['technical_description']]);
        $product->setUsed($line[$columnNums['used']] === 'новая' ? 0 : 1);

        return $product;
    }

    /**
     * Определение номеров строк для дальнейшего сопоставления с полями сущности Product
     */
    private function identifyColumnNumbers($titleCsvRow): array
    {
        $columnNums = [];
        foreach ($titleCsvRow as $num => $title) {
            $columnNums[self::TABLE_TITLE_TO_PRODUCT_FIELD[mb_strtolower($title)]] = $num;
        }

        return $columnNums;
    }
}