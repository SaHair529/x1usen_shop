<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
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
