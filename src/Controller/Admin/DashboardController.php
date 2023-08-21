<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Entity\Order;
use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
//        return parent::index();
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $productsCrudUrl = $routeBuilder->setController(ProductCrudController::class)->generateUrl();

        return $this->redirect($productsCrudUrl);
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    #[Route('/download-invalid-import-lines-file', name: 'download_invalid_import_lines_file')]
    public function downloadInvalidImportLinesFile(): StreamedResponse
    {
        $invalidLinesFilePath = $this->getParameter('kernel.project_dir') . '/var/invalid_lines.json';

        $response = new StreamedResponse(function () use ($invalidLinesFilePath) {
            $fileStream = fopen($invalidLinesFilePath, 'rb');
            fpassthru($fileStream);
            fclose($fileStream);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="invalid_lines.json"');
        $response->headers->set('Content-Length', filesize($invalidLinesFilePath));

        return $response;
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('IGG Motors');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Главная страница', 'fas fa-home', 'homepage');
        yield MenuItem::linkToCrud('Товары', 'fas fa-gears', Product::class);
        yield MenuItem::linkToCrud('Контроль заказов', 'fas fa-solid fa-exclamation', Order::class);
        yield MenuItem::linkToCrud('Оповещения', 'fas fa-solid fa-bell', Notification::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
