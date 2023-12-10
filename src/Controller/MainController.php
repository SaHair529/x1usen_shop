<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchFormType;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use App\Service\Cacher\LaximoCacher;
use App\Service\DataMapping;
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
        private BrandRepository $brandRep,
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
        $brands = $this->brandRep->findBy(['model' => $vehicleModel]);

        if (empty($brands)) {
            $this->addFlash('danger', 'Ничего не найдено');
            return $this->redirectToRoute('homepage');
        }

        $productCategories = [];
        $productArticleNumbers = [];
        foreach ($brands as $brand) {
            $productCategories[$brand->getArticleNumber()] = $brand->getCategory();
            if (!in_array($brand->getArticleNumber(), $productArticleNumbers) && $brand->getArticleNumber())
                $productArticleNumbers[] = $brand->getArticleNumber();
        }

        $abcpArticles = $this->abcpApi->searchProcessor->searchBatchArticlesByNumbers($productArticleNumbers);
        for ($i = 0; $i < count($abcpArticles); $i++) {
            $abcpArticles[$i]['customFields']['category'] = $productCategories[$abcpArticles[$i]['number']];
        }

        $user = $this->getUser();
        $cartItemsArray = [];
        if ($user) {
            $basketArticles = $this->abcpApi->basketProcessor->getBasketArticles($user);
            foreach ($basketArticles as $basketArticle) {
                foreach ($abcpArticles as $abcpArticle) {
                    if ($basketArticle['itemKey'] === $abcpArticle['itemKey'])
                        $cartItemsArray[$abcpArticle['itemKey']] = $basketArticle;
                }
            }
        }

        /** @link DataMapping::$position_description_array_indexes $descriptionArrayIndexes */
        $descriptionArrayIndexes = (new DataMapping())->getData('position_description_array_indexes');

        return $this->render('main/vehicle_model_search_response.html.twig', [
            'products' => $abcpArticles,
            'product_categories' => array_unique(array_values($productCategories)),
            'cart_items' => $cartItemsArray, # todo
            'descriptionArrayIndexes' => $descriptionArrayIndexes
        ]);
    }

    private function handleSearchRequest($queryStr = ''): Response
    {
        $abcpArticles = $this->abcpApi->searchProcessor->searchArticlesByNumber($queryStr);
        $vehicle = $this->lxCacher->getVehicleObjectByVin($queryStr);

        if (false) { # todo replace with $vehicle === null
            try {
                $vehicle = $this->serviceOem->findVehicleByVin($queryStr)->getVehicles()[0] ?? null;
                // $vehicle = unserialize(file_get_contents(__DIR__.'/../../serialized_data/serialized_vehicle.txt')); # todo remove
                $this->lxCacher->setVehicleData($vehicle, $queryStr);
            } catch (InvalidParameterException) {}
        }

        if (false) { # todo replace with $vehicle !== null
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

        $replacementsOems = []; # todo replace with $this->laximoAPIWrapper->getReplacements($queryStr);
        $mainDetails = $this->productRep->findBy(['article_number' => $queryStr]);
        $replacementDetails = $this->productRep->findBy(['article_number' => $replacementsOems]);

        /** @var User $user */
        $user = $this->getUser();
        $cartItemsArray = [];
        if ($user) {
            $basketArticles = $this->abcpApi->basketProcessor->getBasketArticles($user);
            foreach ($basketArticles as $basketArticle) {
                foreach ($abcpArticles as $abcpArticle) {
                    if ($basketArticle['itemKey'] === $abcpArticle['itemKey'])
                        $cartItemsArray[$abcpArticle['itemKey']] = $basketArticle;
                }
            }
        }

        return $this->render('main/oem_search_response.html.twig', [
            'main_details' => $abcpArticles,
            'replacements' => $replacementDetails, # todo разобраться с аналогами
            'query_str' => $queryStr,
            'cart_items' => $cartItemsArray, # todo разобраться c cart_items (нужен ли)
            'descriptionArrayIndexes' => (new DataMapping())->getData('position_description_array_indexes')
        ]);

//        $this->addFlash('danger', 'Ничего не найдено');
//        return $this->render('main/index.html.twig', [
//            'search_form' => $this->createForm(SearchFormType::class)
//        ]);
    }
}
