<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\DataMapping;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use JetBrains\PhpStorm\Pure;

class UserCrudController extends AbstractCrudController
{
    private array $clientTypes;

    #[Pure]
    public function __construct()
    {
        $dataMapping = new DataMapping();
        $this->clientTypes = array_flip($dataMapping->getData('user_client_types'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield Field::new('id')->hideOnForm();
        yield Field::new('username')->setLabel('Логин');
        yield ChoiceField::new('client_type')->setLabel('Тип клиента')->setChoices($this->clientTypes);
        yield AssociationField::new('orders')->setLabel('Заказы')->hideOnForm();
        yield AssociationField::new('cart')->setLabel('Корзина')->hideOnForm();

        yield Field::new('password')->hideOnForm()->hideOnDetail()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        $cartControlAction = Action::new('cartControl', 'Управление корзиной')
            ->linkToCrudAction('cartControl');

        return $actions
            ->add(Crud::PAGE_INDEX, 'detail')
            ->add(Crud::PAGE_INDEX, $cartControlAction)
            ->add(Crud::PAGE_DETAIL, $cartControlAction);
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }
}