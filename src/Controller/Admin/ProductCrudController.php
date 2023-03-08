<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ImportProductsFormType;
use App\Service\CsvProductImporter;
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

    public function importCsv(AdminContext $context, CsvProductImporter $csvImporter)
    {
        $importForm = $this->createForm(ImportProductsFormType::class);
        $importForm->handleRequest($context->getRequest());
        if ($importForm->isSubmitted()) {
            $formData = $importForm->getData();
            /** @var UploadedFile $file */ $file = $formData['csv_file'];
            if ($file->getMimeType() !== 'text/csv') {
                $this->addFlash('danger', 'Неверный формат файла');
                return $this->render('admin/product/import_csv.html.twig', [
                    'form' => $importForm
                ]);
            }
            $csvImporter->importProducts($file);

            return $this->redirectToRoute('admin');
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
