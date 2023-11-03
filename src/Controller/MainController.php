<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use App\Service\Cacher\LaximoCacher;
use App\Service\LaximoAPIWrapper;
use App\Service\ThirdParty\Abcp\AbcpApi;
use GuayaquilLib\exceptions\InvalidParameterException;
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
        private LaximoCacher $lxCacher,
        private AbcpApi $abcpApi
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
        $vehicleModel = $req->query->get('vehicle_model');

        if ($vehicleModel !== null)
            return $this->searchByVehicleModel($vehicleModel);

        if ($queryStr !== null) {
            return $this->handleSearchRequest($queryStr);
        }

        $searchForm = $this->createForm(SearchFormType::class);
        return $this->render('main/index.html.twig', [
            'search_form' => $searchForm
        ]);
    }

    private function searchByVehicleModel(string $vehicleModel)
    {
        $products = $this->productRep->findBy(['auto_model' => $vehicleModel], ['name' => 'ASC']);
        if (empty($products)) {
            $this->addFlash('danger', 'Ничего не найдено');
            return $this->render('main/index.html.twig', [
                'search_form' => $this->createForm(SearchFormType::class)
            ]);
        }

        $productCategories = [];
        foreach ($products as $product) {
            if (!in_array($product->getCategory(), $productCategories) && $product->getCategory())
                $productCategories[] = $product->getCategory();
        }

        $user = $this->getUser();
        $cartItemsArray = [];
        if ($user) {
            $cartItems = $user->getCart()->getItems();
            foreach ($cartItems->getIterator() as $item) {
                foreach ($products as $product) {
                    if (!$item->isInOrder() && $item->getProduct()->getId() === (int)$product->getId()) {
                        $cartItemsArray[$product->getId()] = $item;
                    }
                }
            }
        }

        return $this->render('main/vehicle_model_search_response.html.twig', [
            'products' => $products,
            'product_categories' => $productCategories,
            'cart_items' => $cartItemsArray
        ]);
    }

    private function handleSearchRequest($queryStr = ''): Response
    {
        $abcpArticles = $this->abcpApi->searchProcessor->searchArticlesByNumber($queryStr);
        $vehicle = $this->lxCacher->getVehicleObjectByVin($queryStr);

        if ($vehicle === null) {
            try {
                $vehicle = $this->serviceOem->findVehicleByVin($queryStr)->getVehicles()[0] ?? null;
                // $vehicle = unserialize(file_get_contents(__DIR__.'/../../serialized_data/serialized_vehicle.txt')); # todo remove
                $this->lxCacher->setVehicleData($vehicle, $queryStr);
            } catch (InvalidParameterException) {}
        }

        if ($vehicle !== null) {
            $detailGroups = $this->serviceOem->listQuickGroup(
                $vehicle->getCatalog(),
                $vehicle->getVehicleId(),
                $vehicle->getSsd()
            );

            return $this->render('main/search_response.html.twig', [
                'vehicle' => $vehicle,
                'query_str' => $queryStr,
                'detail_groups' => $detailGroups,
            ]);
        }

        $replacementsOems = $this->laximoAPIWrapper->getReplacements($queryStr);
        $mainDetails = $this->productRep->findBy(['article_number' => $queryStr]);
        $replacementDetails = $this->productRep->findBy(['article_number' => $replacementsOems]);

        $user = $this->getUser();
        $cartItemsArray = [];
        if ($user) {
            $cartItems = $user->getCart()->getItems();
            foreach ($cartItems->getIterator() as $item) {
                foreach ($mainDetails as $product) {
                    if (!$item->isInOrder() && $item->getProduct()->getId() === (int)$product->getId()) {
                        $cartItemsArray[$product->getId()] = $item;
                    }
                }

                foreach ($replacementDetails as $product) {
                    if (!$item->isInOrder() && $item->getProduct()->getId() === (int)$product->getId()) {
                        $cartItemsArray[$product->getId()] = $item;
                    }
                }
            }
        }

        return $this->render('main/oem_search_response.html.twig', [
            'main_details' => $abcpArticles,
            'replacements' => $replacementDetails, # todo разобраться с аналогами
            'query_str' => $queryStr,
            'cart_items' => $cartItemsArray # todo разобраться c cart_items (нужен ли)
        ]);

//        $this->addFlash('danger', 'Ничего не найдено');
//        return $this->render('main/index.html.twig', [
//            'search_form' => $this->createForm(SearchFormType::class)
//        ]);
    }
}
