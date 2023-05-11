<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\User;
use App\Repository\ProductRepository;
use GuayaquilLib\ServiceOem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/details')]
class DetailsController extends AbstractController
{
    #[Route('/list', name: 'list_details')]
    public function listDetails(Request $req, ProductRepository $productRepo): Response
    {
        if (!$req->query->has('catalog') ||
            !$req->query->has('vehicle_id') ||
            !$req->query->has('vehicle_ssd') ||
            !$req->query->has('group_id'))
        {
            return $this->redirectToRoute('homepage');
        }

        $oem = new ServiceOem('ru926364', 'IoOrIIU5_f_HJqT');
        $categories = $oem->listQuickDetail($req->query->get('catalog'),
            $req->query->get('vehicle_id'),
            $req->query->get('vehicle_ssd'),
            $req->query->get('group_id'));

        $products = [];
        foreach ($categories->getCategories() as $detailCategory) {
            foreach ($detailCategory->getUnits() as $detailUnit) {
                foreach ($detailUnit->getParts() as $detailPart) {
                    $product = $productRepo->findOneBy(['article_number' => $detailPart->getOem()]);
                    if ($product !== null && !isset($products[$product->getArticleNumber()]))
                        $products[$product->getArticleNumber()] = $product;
                }
            }
        }
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
}