<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class OrderCrudController extends AbstractCrudController
{
    private const STATUSES = [
        'Новый' => 'new',
        'Ожидание оплаты' => 'wait_payment'
    ];

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield Field::new('id')->hideOnForm();
        yield Field::new('created_at')->setLabel('Дата оформления')->hideOnForm();
        yield Field::new('city')->setLabel('Город')->hideOnForm();
        yield Field::new('address')->setLabel('Адрес')->hideOnForm();
        yield Field::new('phone_number')->setLabel('Номер телефона')->hideOnForm();
        yield ChoiceField::new('status')->setLabel('Статус')->setChoices(self::STATUSES);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(ChoiceFilter::new('status')
                ->setChoices(self::STATUSES)
            );
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
