<?php

namespace App\Controller;

use App\Form\SearchFormType;
use GuayaquilLib\ServiceOem;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Route('/', name: 'homepage')]
    public function index(Request $req): Response
    {
        $queryStr = $req->query->get('query_string');

        if ($queryStr !== null) {
            return $this->handleSearchRequest($queryStr);
        }

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($req);
        if ($searchForm->isSubmitted()) {
            $formData = $searchForm->getData();
            $queryStr = $formData['query_string'];
            return $this->handleSearchRequest($queryStr);
        }
        return $this->render('main/index.html.twig', [
            'search_form' => $searchForm
//            'products' => $productRepo->getPaginator($page)
        ]);
    }

    #[Route('/search', name: 'search')]
    public function search(Request $req): Response
    {
        $queryStr = $req->query->get('query_string');

        if ($queryStr !== null) {
            return $this->handleSearchRequest($queryStr);
        }

        $searchForm = $this->createForm(SearchFormType::class);
        return $this->render('main/index.html.twig', [
            'search_form' => $searchForm
//            'products' => $productRepo->getPaginator($page)
        ]);
    }

    private function handleSearchRequest($queryStr): Response
    {
        if ($queryStr !== null) {
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
                    'query_str' => $queryStr,
                    'detail_groups' => $detailGroups,
                ]);
            }

            return $this->redirectToRoute('detail_page', [
                'article' => $queryStr
            ]);

            $this->addFlash('danger', 'Ничего не найдено');
        }

        $searchForm = $this->createForm(SearchFormType::class);
        return $this->render('main/index.html.twig', [
            'search_form' => $searchForm
//            'products' => $productRepo->getPaginator($page)
        ]);
    }
}
