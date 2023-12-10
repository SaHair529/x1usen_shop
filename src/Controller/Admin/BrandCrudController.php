<?php

namespace App\Controller\Admin;

use App\Entity\Brand;
use App\Form\ImportBrandsFormType;
use App\Repository\BrandRepository;
use App\Service\BrandsImporter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BrandCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Brand::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $importCsvAction = Action::new('import', 'Импорт', 'fas fa-file-excel')
            ->displayAsLink()
            ->setCssClass('btn btn-success')
            ->linkToCrudAction('import')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $importCsvAction);
    }

    public function import(AdminContext $context, BrandRepository $brandRep, UrlGeneratorInterface $urlGenerator, BrandsImporter $brandsImporter): RedirectResponse|Response
    {
        $importForm = $this->createForm(ImportBrandsFormType::class);
        $importForm->handleRequest($context->getRequest());

        if ($importForm->isSubmitted()) {
            $formData = $importForm->getData();
            /** @var UploadedFile $file */ $file = $formData['spreadsheet_file'];
            $fileExtension = $file->getClientOriginalExtension();
            if ($fileExtension !== 'csv' && $fileExtension !== 'xls' && $fileExtension !== 'xlsx') {
                $this->addFlash('danger', 'Неверный формат файла');
                return $this->render('admin/brand/import.html.twig', [
                    'form' => $importForm
                ]);
            }

            try {
                if ($fileExtension === 'csv')
                    $invalidLines = $brandsImporter->importBrandsByCsv($file);
                else
                    $invalidLines = $brandsImporter->importBrandsByXls($file);

                if (!empty($invalidLines)) {
                    $invalidLinesFilePath = $this->getParameter('kernel.project_dir') . '/var/invalid_lines.json';
                    $fileStream = fopen($invalidLinesFilePath, 'wb');
                    fwrite($fileStream, json_encode($invalidLines, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    fclose($fileStream);

                    $downloadUrl = $urlGenerator->generate('download_invalid_import_lines_file', [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $this->addFlash('warning',
                        'В таблице есть невалидные строки. Чтобы загрузить файл с информацией о них, нажмите '.
                        '<a href="'.$downloadUrl.'">здесь</a>');
                }
            }
            catch (Exception $ex) {
                $this->addFlash('danger', $ex->getMessage());
            }

            return $this->redirectToRoute('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => 'App\\Controller\\Admin\\BrandCrudController'
            ]);
        }

        return $this->render('admin/brand/import.html.twig', [
            'form' => $importForm
        ]);
    }
}