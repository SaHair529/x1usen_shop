<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use App\Service\LaximoAPIWrapper;
use GuayaquilLib\ServiceOem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    private ServiceOem $serviceOem;

    public function __construct(
        private LaximoAPIWrapper $laximoAPIWrapper,
        private ProductRepository $productRep,
    )
    {
        $this->serviceOem = new ServiceOem($_ENV['OEM_LOGIN'], $_ENV['OEM_PASSWORD']);
    }

    #[Route('/', name: 'homepage')]
    public function index(Request $req): Response
    {
        $queryStr = trim($req->query->get('query_string'));
        if ($queryStr) {
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
        $nothingFound = false;
        if ($queryStr !== null) {
            // vin Z94K241CBMR252528
//            $vehicle = $oemService->findVehicle($queryStr)->getVehicles()[0] ?? []; # todo uncomment
//            file_put_contents(__DIR__.'/../../serialized_data/serialized_vehicle.txt', serialize($vehicle)); # todo remove
            $vehicle = unserialize(file_get_contents(__DIR__.'/../../serialized_data/serialized_vehicle.txt')); # todo remove
//            $vehicle = []; // todo remove
            if (!empty($vehicle)) {
                $detailGroups = $this->serviceOem->listQuickGroup($vehicle->getCatalog(), $vehicle->getVehicleId(), $vehicle->getSsd());
                return $this->render('main/search_response.html.twig', [
                    'vehicle' => $vehicle,
                    'query_str' => $queryStr,
                    'detail_groups' => $detailGroups,
                ]);
            }

            $replacementsOems = $this->laximoAPIWrapper->getReplacements($queryStr);
            $mainDetails = $this->productRep->findBy(['article_number' => $queryStr]);
            $replacementDetails = $this->productRep->findBy(['article_number' => $replacementsOems]);

            if ($mainDetails || $replacementDetails)
                return $this->render('main/oem_search_response.html.twig', [
                    'main_details' => $mainDetails,
                    'replacements' => $replacementDetails,
                    'query_str' => $queryStr
                ]);

            $this->addFlash('danger', 'Ничего не найдено');
        }

        $searchForm = $this->createForm(SearchFormType::class);
        return $this->render('main/index.html.twig', [
            'search_form' => $searchForm
        ]);
    }
}
