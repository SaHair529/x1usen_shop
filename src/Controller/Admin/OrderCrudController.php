<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Service\DataMapping;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use JetBrains\PhpStorm\Pure;

class OrderCrudController extends AbstractCrudController
{
    private array $statuses;

    #[Pure]
    public function __construct()
    {
        $dataMapping = new DataMapping();
        $this->statuses = array_flip($dataMapping->getData('order_statuses'));
    }

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
        yield ChoiceField::new('status')->setLabel('Статус')->setChoices($this->statuses);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(ChoiceFilter::new('status')
                ->setChoices($this->statuses)
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
