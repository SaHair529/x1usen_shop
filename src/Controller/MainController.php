<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use App\Service\Cacher\LaximoCacher;
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
        private LaximoCacher $lxCacher
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
        ]);
    }

    private function handleSearchRequest($queryStr): Response
    {
        if ($queryStr !== null) {
            $vehicle = $this->lxCacher->getVehicleObjectByVin($queryStr);
            if ($vehicle === null) {
                $vehicle = $this->serviceOem->findVehicleByVin($queryStr)->getVehicles()[0] ?? null;
//                $vehicle = unserialize(file_get_contents(__DIR__.'/../../serialized_data/serialized_vehicle.txt')); # todo remove
                if ($vehicle !== null) {
                    $this->lxCacher->setVehicleData($vehicle, $queryStr);
                }
            }

            if ($vehicle !== null) {
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
