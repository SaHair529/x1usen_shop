<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use GuayaquilLib\Am;
use GuayaquilLib\objects\oem\VehicleListObject;
use GuayaquilLib\ServiceAm;
use GuayaquilLib\ServiceOem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Request $req, ProductRepository $productRepo): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($req);
        if ($searchForm->isSubmitted()) {
            $formData = $searchForm->getData();
            $queryStr = $formData['query_string'];

            $oem = new ServiceOem('ru926364', 'IoOrIIU5_f_HJqT');
            // vin Z94K241CBMR252528
//            $vehicle = $oem->findVehicle($queryStr)->getVehicles()[0] ?? []; # todo uncomment
//            file_put_contents(__DIR__.'/serialized_vehicle.txt', serialize($vehicle)); # todo remove
            $vehicle = unserialize(file_get_contents(__DIR__.'/serialized_vehicle.txt')); # todo remove
            $detailGroups = $oem->listQuickGroup($vehicle->getCatalog(), $vehicle->getVehicleId(), $vehicle->getSsd());
            return $this->render('main/search_response.html.twig', [
                'vehicle' => $vehicle,
                'detail_groups' => $detailGroups,
            ]);
        }
        return $this->render('main/index.html.twig', [
            'search_form' => $searchForm
//            'products' => $productRepo->getPaginator($page)
        ]);
    }

    #[Route('/product/{product_slug}', name: 'product_page')]
    public function product(string $productSlug): Response
    {
        return new Response($productSlug);
    }

    #[Route('/categories', name: 'categories_page')]
    public function categories(): Response
    {
        return new Response('Categories');
    }

    #[Route('/category/{category_slug}', name: 'category_page')]
    public function category(string $categorySlug): Response
    {
        return new Response($categorySlug);
    }
}
