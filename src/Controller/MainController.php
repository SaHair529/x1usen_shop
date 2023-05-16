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
use function PHPUnit\Framework\isNull;

class MainController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Request $req, ProductRepository $productRep): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($req);
        if ($searchForm->isSubmitted()) {
            $formData = $searchForm->getData();
            $queryStr = $formData['query_string'];

            $oemService = new ServiceOem('ru926364', 'IoOrIIU5_f_HJqT');
            // vin Z94K241CBMR252528
//            $vehicle = $oem->findVehicle($queryStr)->getVehicles()[0] ?? []; # todo uncomment
//            file_put_contents(__DIR__.'/serialized_vehicle.txt', serialize($vehicle)); # todo remove
            $vehicle = unserialize(file_get_contents(__DIR__.'/serialized_vehicle.txt')); # todo remove
//            $vehicle = []; // todo remove
            if (!empty($vehicle)) {
                $detailGroups = $oemService->listQuickGroup($vehicle->getCatalog(), $vehicle->getVehicleId(), $vehicle->getSsd());
                return $this->render('main/search_response.html.twig', [
                    'vehicle' => $vehicle,
                    'detail_groups' => $detailGroups,
                ]);
            }

            return $this->redirectToRoute('detail_page', [
                'article' => $queryStr
            ]);

            $this->addFlash('danger', 'Ничего не найдено');
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
