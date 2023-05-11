<?php

namespace App\ControllerHelper\CartController;

use App\Entity\CartItem;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseCreator
{
    public static function addItem_ok(CartItem $cartItem, Product $product): JsonResponse
    {
        return new JsonResponse([
            'message' => 'ok',
            'quantity' => $cartItem->getQuantity(),
            'product_price' => $product->getPrice(),
            'product_total_balance' => $product->getTotalBalance(),
            'has_more_product' => $product->getTotalBalance() > 0,
        ]);
    }

    public static function ok(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'ok'
        ]);
    }

    public static function addItem_productNotFound(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'product not found'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function decreaseQuantity_ok($currentQuantity, $productPrice, $productTotalBalance): Response
    {
        return new JsonResponse([
            'message' => 'ok',
            'quantity' => $currentQuantity,
            'product_price' => $productPrice,
            'product_total_balance' => $productTotalBalance
        ]);
    }

    public static function decreaseQuantity_cartItemNotFound(): Response
    {
        return new JsonResponse([
            'message' => 'cart item or its related product not found',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function getProductRelatedCartItem_ok(CartItem $item): JsonResponse
    {
        return new JsonResponse([
            'id' => $item->getId(),
            'quantity' => $item->getQuantity(),
            'has_more_product' => $item->getProduct()->getTotalBalance() > 0
        ]);
    }

    public static function getProductRelatedCartItem_cartItemNotFound(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'cart item of product not found'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function notAuthorized(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'not authorized'
        ], Response::HTTP_FORBIDDEN);
    }

    public static function outOfStock(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'out of stock'
        ]);
    }
}