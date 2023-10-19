<?php

namespace App\Controller\Admin;

use App\Entity\User;

class UserCrudController extends \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}