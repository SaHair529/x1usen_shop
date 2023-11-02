<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use App\Service\ThirdParty\Abcp\AbcpApi;
use GuayaquilLib\ServiceOem;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/details')]
class DetailsController extends AbstractController
{
    private ServiceOem $serviceOem;

    public function __construct()
    {
        $this->serviceOem = new ServiceOem($_ENV['OEM_LOGIN'], $_ENV['OEM_PASSWORD']);
    }

    /**
     * Отдельная страница с информацией об определённом товаре
     */
    #[Route('/item/{id}', name: 'detail_page')]
    #[IsGranted('ROLE_USER')]
    public function show($id, ProductRepository $productRep): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();
        $inCartQuantity = 0;
        foreach ($cartItems as $cartItem) {
            if ($cartItem->getProduct()->getId() === $id) {
                $inCartQuantity = $cartItem->getQuantity();
            }
        }

        $product = $productRep->find($id);
        return $this->render('details/detail_page.html.twig', [
            'product' => $product,
            'in_cart_quantity' => $inCartQuantity
        ]);
    }

    /**
     * Кусок html-блока с информацией о товаре
     */
    #[Route('/{articleNumber}', name: 'detail_info')]
    public function info($articleNumber, Request $request, AbcpApi $abcpApi): Response
    {
        $foundArticle = $abcpApi->getConcreteArticleByItemKeyAndNumber($request->query->get('itemKey'), $articleNumber);

        return $this->render('details/detail_info.html.twig', [
            'product' => $foundArticle
        ]);
    }

    #[Route('/ajax/brands', name: 'detail_brands')]
    public function brands(BrandRepository $brandRep, SerializerInterface $serializer): JsonResponse
    {
        return (new JsonResponse(json_decode($serializer->serialize($brandRep->findAll(), 'json'))));
    }

    #[Route('/ajax/brand_models/{brand}', name: 'detail_brand_models')]
    public function brandModels(string $brand, ProductRepository $productRep): JsonResponse
    {
        if ($brand == null)
            return new JsonResponse([
                'message' => 'Invalid request data (brand must be stringable)',
            ], Response::HTTP_BAD_REQUEST);

        $models = [];
        $products = $productRep->findBy(['auto_brand' => $brand]);

        foreach ($products as $product) {
            if (!in_array($product->getAutoModel(), $models) && !empty($product->getAutoModel()))
                $models[] = $product->getAutoModel();
        }

        return new JsonResponse($models, Response::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    #[Route('/ajax/categories', name: 'details_list_categories')]
    public function listCategories(Request $req): Response
    {
        if (!$req->query->has('catalog') ||
            !$req->query->has('vehicle_id') ||
            !$req->query->has('vehicle_ssd') ||
            !$req->query->has('group_id'))
        {
            return new JsonResponse([
                'message' => 'invalid request'
            ]);
        }

        $categories = $this->serviceOem->listQuickDetail($req->query->get('catalog'),
            $req->query->get('vehicle_id'),
            $req->query->get('vehicle_ssd'),
            $req->query->get('group_id'))->getCategories();

        $unitImageMaps = [];
        foreach ($categories as $category) {
            foreach ($category->getUnits() as $unit) {
                $unitImageMap = $this->serviceOem->listImageMapByUnit($req->query->get('catalog'), $unit->getSsd(), $unit->getUnitId());
                if (array_key_exists($unit->getUnitId(), $unitImageMaps))
                    throw new \Exception('В $unitImageMaps уже есть элемент с ключом '.$unit->getUnitId());

                $unitImageMaps[$unit->getUnitId()] = $unitImageMap;
            }
        }

        return $this->render('details/categories-list.html.twig', [
            'categories' => $categories,
            'unit_image_maps' => $unitImageMaps
        ]);
    }

    #[Route('/ajax/details', name: 'details_list_details')]
    public function listDetails(Request $req, ProductRepository $productRep): Response
    {
        $oems = explode(',', json_decode($req->getContent(), true)['oems'] ?? '');
        if (empty($oems)) {
            return new JsonResponse([
                'message' => 'Invalid request data',
                'valid data example' => '{"oems":"1125406206B,1420608250B,1471175006B"}'
            ]);
        }

        $products = $productRep->findBy(['article_number' => $oems]);
        return $this->render('details/details-list.html.twig', [
            'products' => $products
        ]);
    }
}