<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use GuayaquilLib\ServiceOem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/{id}', name: 'detail_page')]
    public function info($id, ProductRepository $productRep): Response
    {
        if (!is_numeric($id))
            return $this->redirectToRoute('homepage');

        $product = $productRep->find($id);
        return $this->render('details/detail_page.html.twig', [
            'product' => $product
        ]);
    }
}