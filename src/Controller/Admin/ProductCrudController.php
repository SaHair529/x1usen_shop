<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ImportProductsFormType;
use App\Repository\BrandRepository;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $importCsvAction = Action::new('importCsv', 'Импорт', 'fas fa-file-excel')
            ->displayAsLink()
            ->setCssClass('btn btn-success')
            ->linkToCrudAction('importCsv')
            ->createAsGlobalAction();


        return $actions
            ->add(Crud::PAGE_INDEX, $importCsvAction)
            ->update(Crud::PAGE_INDEX, Action::NEW, function ($action) {
                return $action->setLabel('Добавить');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function ($action) {
                return $action->setLabel('Изменить');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function ($action) {
                return $action->setLabel('Удалить');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function ($action) {
                return $action->setLabel('Сохранить');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function ($action) {
                return $action->setLabel('Сохранить и продолжить изменения');
            });
    }

    public function importCsv(AdminContext $context, CsvProductImporter $csvImporter, BrandRepository $brandRep): RedirectResponse|Response
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
            $csvImporter->importProducts($file, $brandRep);

            return $this->redirectToRoute('admin');
        }
        return $this->render('admin/product/import_csv.html.twig', [
            'form' => $importForm
        ]);
    }
}
