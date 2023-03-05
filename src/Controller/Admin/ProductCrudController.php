<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ImportProductsFormType;
use App\Repository\ProductRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $importCsvAction = Action::new('importCsv', 'Import', 'fas fa-file-excel')
            ->displayAsLink()
            ->setCssClass('btn btn-success')
            ->linkToCrudAction('importCsv')
            ->createAsGlobalAction();


        return $actions
            ->add(Crud::PAGE_INDEX, $importCsvAction);
    }

    public function importCsv(AdminContext $context, ProductRepository $productRepo)
    {
        $importForm = $this->createForm(ImportProductsFormType::class);
        $importForm->handleRequest($context->getRequest());
        if ($importForm->isSubmitted()) {
            $formData = $importForm->getData();
            /** @var UploadedFile $file */ $file = $formData['csv_file'];
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
                $productRepo->save($product, $flush);
            }
            dd(123);
        }
        return $this->render('admin/product/import_csv.html.twig', [
            'form' => $importForm
        ]);
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
