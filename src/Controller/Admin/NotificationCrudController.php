<?php

namespace App\Controller\Admin;

use App\Entity\Notification;
use App\Service\DataMapping;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class NotificationCrudController extends AbstractCrudController
{
    private $notificationActions;

    public function __construct()
    {
        $dataMapping = new DataMapping();
        $this->notificationActions = array_flip($dataMapping->getData('notification_actions'));
    }

    public static function getEntityFqcn(): string
    {
        return Notification::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('updated_order')->setLabel('Ссылка на заказ')->hideOnForm();
        yield ChoiceField::new('action')->setLabel('Событие')->setChoices($this->notificationActions);
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
