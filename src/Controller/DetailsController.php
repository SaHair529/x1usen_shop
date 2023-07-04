<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
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

        return $this->render('details/categories-list.html.twig', [
            'categories' => $categories
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

    #[Route('/{id}', name: 'detail_info')]
    public function info($id, ProductRepository $productRep): Response
    {
        if (!is_numeric($id))
            return $this->redirectToRoute('homepage');

        $product = $productRep->find($id);
        return $this->render('details/detail_info.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/item/{article}', name: 'detail_page')]
    #[IsGranted('ROLE_USER')]
    public function show($article, ProductRepository $productRep): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $cartItems = $user->getCart()->getItems();
        $inCartQuantity = 0;
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->getProduct();
            $productArticle = $product->getArticleNumber();
            if ($productArticle === $article) {
                $inCartQuantity = $cartItem->getQuantity();
            }
        }

        $product = $productRep->findOneBy(['article_number' => $article]);
        return $this->render('details/detail_page.html.twig', [
            'product' => $product,
            'in_cart_quantity' => $inCartQuantity
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
}