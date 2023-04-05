<?php

namespace App\ControllerHelper\CartController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseCreator
{
    public static function decreaseQuantity_ok($currentQuantity): Response
    {
        return new JsonResponse([
            'message' => 'ok',
            'current_quantity' => $currentQuantity
        ]);
    }

    public static function decreaseQuantity_cartItemNotFound(): Response
    {
        return new JsonResponse([
            'message' => 'cart item or its related product not found',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}