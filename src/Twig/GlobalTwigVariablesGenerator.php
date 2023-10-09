<?php

namespace App\Twig;

use App\Entity\CartItem;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Класс, генерирующий динамические глобальные переменные для всех Twig шаблонов
 */
class GlobalTwigVariablesGenerator
{
    public function __construct(private Security $security)
    {
    }

    /**
     * Возвращает количество товаров в корзине (Нужно для отображения в иконке корзины)
     * @return int
     */
    public function currentUserActiveCartItemsAmount (): int
    {
        $result = 0;

        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user)
            return $result;

        /** @var CartItem $cartItem */
        foreach ($user->getCart()->getItems() as $cartItem) {
            if (!$cartItem->isInOrder())
                $result++;
        }

        return $result;
    }
}